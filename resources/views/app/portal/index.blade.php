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
        tab: @js($activeTab),
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

        // Workout Video Modal State
        activeVideo: null,

        openExerciseVideo(payload) {
            if (!payload || !payload.url || !/^(\/|https?:\/\/)/i.test(payload.url)) {
                return;
            }
            if (!payload.sets && payload.setsReps) {
                const parts = String(payload.setsReps).split(/[×*xX]/);
                if (parts.length >= 2) {
                    payload.sets = parts[0].replace(/[^0-9]/g, '').trim() || parts[0].trim();
                    payload.reps = parts.slice(1).join('×').trim();
                } else {
                    payload.reps = payload.setsReps;
                }
            }
            this.activeVideo = payload;
        },

        closeExerciseVideo() {
            const vid = document.getElementById('portal-exercise-video-player');
            if (vid) {
                try { vid.pause(); } catch (e) {}
            }
            this.activeVideo = null;
        },

        resolveVideoKind(url) {
            if (!url) return 'none';
            if (/\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(url)) return 'mp4';
            if (/(youtube\.com\/(watch|shorts|embed)|youtu\.be\/|vimeo\.com\/)/i.test(url)) return 'embed';
            return 'link';
        },

        resolveEmbedUrl(url) {
            if (!url) return '';
            let match = url.match(/(?:youtube\.com\/(?:watch\?.*v=|shorts\/|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/i);
            if (match && match[1]) {
                return 'https://www.youtube.com/embed/' + match[1] + '?rel=0&playsinline=1&autoplay=1';
            }
            match = url.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
            if (match && match[1]) {
                return 'https://player.vimeo.com/video/' + match[1] + '?autoplay=1';
            }
            return url;
        },

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
            if (name !== 'workouts' && this.activeVideo) {
                this.closeExerciseVideo();
            }
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
    class="member-portal max-w-xl md:max-w-3xl mx-auto space-y-4 pb-24 sm:pb-16 pt-1 min-w-0">

        @if(! $member)
            <!-- Unlinked Account Notice -->
            <div class="p-8 rounded-[10px] bg-[#141518] border border-[#26282D] shadow-none text-center space-y-4">
                <div class="w-16 h-16 rounded-[10px] bg-amber-500/10 text-amber-400 flex items-center justify-center mx-auto border border-amber-500/20">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-xl font-semibold text-white">Welcome, {{ auth()->user()->name }}!</h2>
                <p class="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">Your member account is active, but your mobile number has not yet been linked to an active gym profile. Please contact the front desk reception to verify your registration.</p>
            </div>
        @else

            <!-- APP SEGMENTED TABS (DESKTOP TOP / MOBILE BOTTOM BAR) -->
            <div class="{{ auth()->user()->isMember() ? 'hidden' : 'hidden lg:flex' }} items-center gap-1.5 p-1.5 rounded-[10px] bg-[#0D0E10]/95 dark:bg-[#0D0E10]/95 border border-[#26282D] shadow-none text-xs font-semibold">
                <button type="button" 
                        @click="setPortalTab('overview')" 
                        :class="tab === 'overview' ? 'bg-[#F5A524] text-neutral-950 font-semibold shadow-none ' : 'text-slate-400 hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-[10px] transition-all cursor-pointer">
                    Home
                </button>
                <button type="button" 
                        @click="setPortalTab('workouts')" 
                        :class="tab === 'workouts' ? 'bg-[#F5A524] text-neutral-950 font-semibold shadow-none ' : 'text-slate-400 hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-[10px] transition-all cursor-pointer">
                    Workout
                </button>
                <button type="button" 
                        @click="setPortalTab('diets')" 
                        :class="tab === 'diets' ? 'bg-[#F5A524] text-neutral-950 font-semibold shadow-none ' : 'text-slate-400 hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-[10px] transition-all cursor-pointer">
                    Nutrition
                </button>
                <button type="button" 
                        @click="setPortalTab('pt')" 
                        :class="tab === 'pt' ? 'bg-[#F5A524] text-neutral-950 font-semibold shadow-none ' : 'text-slate-400 hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-[10px] transition-all cursor-pointer">
                    Coaching
                </button>
                <button type="button" 
                        @click="setPortalTab('memberships')" 
                        :class="tab === 'memberships' || tab === 'attendance' ? 'bg-[#F5A524] text-neutral-950 font-semibold shadow-none ' : 'text-slate-400 hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-[10px] transition-all cursor-pointer">
                    Membership
                </button>
            </div>

            <!-- ========================================================= -->
            <!-- LIVE PENDING PT SESSION NOTIFICATION BANNER               -->
            <!-- ========================================================= -->
            <template x-if="activePendingPt">
                <div class="relative overflow-hidden rounded-[10px] bg-[#141518] border-2 border-[#F5A524]/60 p-4 sm:p-5 shadow-none text-white space-y-3.5 transition-all">
                    <!-- Glow background -->
                    

                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-[10px] bg-[#F5A524] text-neutral-950 font-semibold flex items-center justify-center text-xl shrink-0 shadow-none ">
                                <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m13 2-9 12h7l-1 8 10-12h-7z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-full bg-[#F5A524] text-neutral-950 text-[10px] font-semibold uppercase tracking-wider">Coach Waiting</span>
                                    <span class="text-[10px] font-semibold text-[#F5A524] flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#F5A524] "></span>
                                        <span>Confirm your workout session</span>
                                    </span>
                                </div>
                                <h3 class="text-sm sm:text-base font-semibold truncate mt-1 text-white">
                                    Session with <span class="text-[#F5A524]" x-text="activePendingPt.trainer_name"></span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    <span x-text="activePendingPt.package_name"></span> &bull; <span x-text="activePendingPt.duration_minutes + ' Mins'"></span>
                                </p>
                            </div>
                        </div>

                        <template x-if="activePendingPt.otp_code">
                            <div class="text-right shrink-0 bg-[#0D0E10] px-3 py-1.5 rounded-[10px] border border-[#F5A524]/40 ">
                                <span class="text-[10px] uppercase font-semibold text-slate-400 block">Session PIN</span>
                                <span class="text-base font-mono font-semibold text-[#F5A524] tracking-wider" x-text="activePendingPt.otp_code"></span>
                            </div>
                        </template>
                    </div>

                    <div class="relative z-10 grid grid-cols-2 gap-2.5 pt-1">
                        <button type="button" 
                                @click="openPtConfirmModal()" 
                                class="py-2.5 px-3 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414] text-neutral-950 font-semibold text-xs shadow-none  flex items-center justify-center gap-1.5 cursor-pointer transition-all ">
                            <span>✓ Confirm Session</span>
                        </button>
                        <button type="button" 
                                @click="startPortalScanner('camera')" 
                                class="py-2.5 px-3 rounded-[10px] bg-[#141518] hover:bg-[#1B1D21] text-white font-semibold text-xs border border-slate-700 flex items-center justify-center gap-1.5 cursor-pointer transition-all ">
                            <svg class="w-4 h-4 text-[#F5A524] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Scan QR Code</span>
                        </button>
                    </div>
                </div>
            </template>

            <!-- TAB 1: HOME / OVERVIEW -->
            <div x-show="tab === 'overview'" x-cloak class="space-y-4">
                <div class="flex items-center justify-between gap-3 px-0.5">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($member->avatar_url)
                            <img src="{{ $member->avatar_url }}" alt="" class="w-11 h-11 rounded-full object-cover border border-neutral-700 shrink-0">
                        @else
                            <div class="w-11 h-11 rounded-full bg-[#1B1D21] border border-neutral-700 flex items-center justify-center text-sm font-semibold text-[#F5A524] shrink-0">{{ strtoupper(substr($member->first_name ?: 'M', 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-xs text-slate-400">Welcome back</p>
                            <h1 class="text-lg sm:text-xl font-semibold text-white break-words">{{ $member->full_name }}</h1>
                        </div>
                    </div>
                    <button type="button" @click="startPortalScanner('camera')" aria-label="Open scanner" class="w-11 h-11 rounded-[10px] border border-neutral-700 bg-[#141518] text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 9V4h5m6 0h5v5m0 6v5h-5m-6 0H4v-5M8 12h8"/></svg>
                    </button>
                </div>

                <!-- HERO: LUXURY DIGITAL MEMBERSHIP PASS (ATHLETIC STEALTH) -->
            <div class="relative overflow-hidden rounded-[10px] bg-[#141518] border border-[#F5A524]/30 shadow-none p-5 sm:p-6 text-white space-y-5">
                <!-- Ambient Glow Elements -->
                
                

                <!-- Card Top: Gym Branding & Status Badge -->
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if(!empty($resolvedGymLogo))
                            <img src="{{ $resolvedGymLogo }}" alt="{{ $gymName }}" class="h-6 w-auto max-w-[80px] rounded-md object-contain bg-white/10 p-0.5">
                        @else
                            <span class="w-6 h-6 rounded-lg bg-[#F5A524] text-neutral-950 font-semibold text-[10px] flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($gymName, 0, 2)) }}
                            </span>
                        @endif
                        <span class="text-xs font-semibold tracking-wide uppercase text-[#F5A524]">
                            {{ $gymName }}
                        </span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $member->status === 'ACTIVE' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25' : ($member->status === 'SUSPENDED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-700/50 text-slate-300 border border-slate-600/40') }}">
                        {{ $member->status }}
                    </span>
                </div>

                <!-- Compact membership summary -->
                <div class="relative z-10 flex items-center justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <p class="text-xs text-slate-400">Member ID</p>
                        <p class="text-xl font-semibold text-white tabular-nums break-words">{{ $member->member_code }}</p>
                        <p class="text-sm text-slate-300 break-words">{{ $activeMembership?->plan?->name ?? 'No active plan' }}</p>
                        @if($member->branch)
                            <p class="text-xs text-slate-400">{{ $member->branch->name }}</p>
                        @endif
                    </div>
                    <button type="button" @click="showQrModal = true" aria-label="View digital pass" class="w-16 h-16 rounded-[10px] bg-[#1B1D21] border border-neutral-700 flex items-center justify-center shrink-0 text-[#F5A524]">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h4v4h-7v-4m7-3v1"/></svg>
                    </button>
                </div>

                <!-- Card Validity & Days Remaining Pill -->
                @if($activeMembership)
                    <div class="relative z-10 pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px]">Valid Until:</span>
                            <span class="font-semibold text-white ml-1">{{ $activeMembership->end_date?->format('d M, Y') ?? 'N/A' }}</span>
                        </div>
                        @if($daysLeft !== null)
                            <span class="px-3 py-1 rounded-[10px] text-xs font-semibold {{ $daysLeft > 7 ? 'bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30' : ($daysLeft >= 0 ? 'bg-amber-400/15 text-amber-300 border border-amber-400/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30') }}">
                                {{ $daysLeft >= 0 ? ($daysLeft . ' Days Left') : ('Expired ' . abs($daysLeft) . 'd ago') }}
                            </span>
                        @endif
                    </div>
                @endif

                <!-- Card Action Buttons: 1-Tap Check-in & Pass -->
                <div class="relative z-10 grid grid-cols-2 gap-2.5 pt-1">
                    <button type="button" 
                            @click="startPortalScanner('camera')" 
                            class="py-3 px-3 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414]  text-neutral-950 font-semibold text-xs shadow-none  flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Check-in / PT</span>
                    </button>

                    <button type="button" 
                            @click="showQrModal = true" 
                            class="py-3 px-3 rounded-[10px] bg-white/10 hover:bg-white/15  border border-white/15 text-white font-semibold text-xs shadow-none flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-[#F5A524] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>View Pass</span>
                    </button>
                </div>
            </div>

            <!-- TODAY'S CHECK-IN STATUS STRIP -->
            <div class="px-4 py-3 rounded-[10px] {{ $todayCheckin ? 'bg-[#F5A524]/10 border border-[#F5A524]/25 text-[#F5A524]' : 'bg-[#141518] border border-[#26282D] text-slate-300' }} flex items-center justify-between text-xs font-semibold ">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $todayCheckin ? 'bg-[#F5A524] ' : 'bg-slate-500' }}"></span>
                    @if($todayCheckin)
                        <span>Checked in today at {{ $todayCheckin->check_in ? \Carbon\Carbon::parse($todayCheckin->check_in)->format('h:i A') : 'Recorded' }}</span>
                    @else
                        <span>Not checked in yet today</span>
                    @endif
                </div>
                <button type="button" @click="setPortalTab('attendance')" class="text-[11px] font-semibold text-slate-400 hover:text-[#F5A524] transition-colors">
                    {{ $thisMonthCheckins }} visits this month &rarr;
                </button>
            </div>

                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                    <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-3 min-w-0">
                        <p class="text-xs text-slate-400">Attendance</p>
                        <p class="mt-1 text-lg sm:text-xl font-semibold text-white tabular-nums">{{ $thisMonthCheckins }}</p>
                        <p class="text-xs text-slate-400">This month</p>
                    </div>
                    <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-3 min-w-0">
                        <p class="text-xs text-slate-400">Goal progress</p>
                        <p class="mt-1 text-lg sm:text-xl font-semibold text-white tabular-nums">{{ $goalPercent }}%</p>
                        <p class="text-xs text-slate-400">Monthly</p>
                    </div>
                    <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-3 min-w-0">
                        <p class="text-xs text-slate-400">Target</p>
                        <p class="mt-1 text-lg sm:text-xl font-semibold text-white tabular-nums">{{ $monthlyGoal }}</p>
                        <p class="text-xs text-slate-400">Visits</p>
                    </div>
                </div>
            <!-- Attendance and weekly consistency -->
            <section class="rounded-[10px] bg-[#141518] border border-[#26282D] p-4 space-y-4 text-white">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 class="text-lg font-semibold">Attendance</h2>
                    <span class="text-xs text-slate-400">{{ now()->format('F Y') }}</span>
                </div>
                <div class="space-y-2">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <span class="text-sm text-slate-300">{{ $thisMonthCheckins }} of {{ $monthlyGoal }} visits</span>
                        <span class="text-xs text-slate-400">{{ $sessionsNeeded > 0 ? $sessionsNeeded . ' remaining' : 'Goal reached' }}</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-[#26282D] overflow-hidden" role="progressbar" aria-label="Monthly attendance target" aria-valuenow="{{ $goalPercent }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full bg-[#F5A524]" style="width: {{ $goalPercent }}%"></div>
                    </div>
                </div>
                <!-- Weekly Consistency Dots (Mon - Sun Streak) -->
                <div class="relative z-10 pt-2 border-t border-[#26282D]/80">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1">
                            <span>This week</span>
                        </span>
                        <span class="text-[10px] text-slate-400">
                            {{ count(array_filter($currentWeekDays, fn($d) => $d['attended'])) }} / 7 Days Active
                        </span>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center">
                        @foreach($currentWeekDays as $day)
                            <div class="py-2 px-1 rounded-[10px] flex flex-col items-center justify-center gap-1 border transition-all {{ $day['attended'] ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : ($day['isToday'] ? 'bg-[#F5A524]/10 border-[#F5A524]/30 text-[#F5A524] ring-1 ring-[#F5A524]/25' : 'bg-[#0D0E10]/50 border-[#26282D]/80 text-slate-500') }}">
                                <span class="text-[10px] font-semibold uppercase">{{ $day['day'] }}</span>
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold {{ $day['attended'] ? 'bg-emerald-500/15 text-emerald-400' : ($day['isToday'] ? 'border-2 border-[#F5A524] text-[#F5A524]' : 'bg-[#1B1D21] text-slate-400') }}">
                                    @if($day['attended'])
                                        ✓
                                    @else
                                        {{ $day['date'] }}
                                    @endif
                                </div>
                                <span class="text-[10px] font-semibold uppercase tracking-tight">
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
            </section>

                <!-- TODAY'S ROUTINE: WORKOUT & NUTRITION (BALANCED 2-COL GRID) -->
                <h2 class="text-lg font-semibold text-white">Today's plan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Today's Workout Quick Card -->
                    @php
                        $homeWorkoutDayLabels = [
                            'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
                            'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
                            'day_1' => 'Day 1', 'day_2' => 'Day 2', 'day_3' => 'Day 3',
                            'day_4' => 'Day 4', 'day_5' => 'Day 5', 'day_6' => 'Day 6', 'day_7' => 'Day 7',
                        ];
                        $homeDayKey = null;
                        $homeDayExercises = collect();
                        if ($activeWorkout && $activeWorkout->exercises->isNotEmpty()) {
                            $groupedHomeEx = $activeWorkout->exercises->sortBy('sort_order')->groupBy('day');
                            $todayWeekdayKey = strtolower(now()->format('l'));
                            $homeDayKey = $groupedHomeEx->has($todayWeekdayKey) ? $todayWeekdayKey : $groupedHomeEx->keys()->first();
                            $homeDayExercises = $groupedHomeEx->get($homeDayKey, collect());
                        }
                    @endphp
                    <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-5  space-y-3.5 flex flex-col justify-between">
                        <div class="space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center font-semibold text-base shrink-0 border border-[#F5A524]/30">
                                        <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9v6m3-8v10m12-10v10m3-8v6M6 12h12"/></svg>
                                    </div>
                                    <div class="min-w-0 space-y-0.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h3 class="text-sm font-semibold text-white tracking-tight truncate">Workout Routine</h3>
                                            @if($homeDayKey)
                                                <span class="px-1.5 py-0.5 rounded-md bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/25 text-[10px] font-semibold uppercase tracking-wider shrink-0">
                                                    {{ $homeWorkoutDayLabels[$homeDayKey] ?? ucfirst(str_replace('_', ' ', $homeDayKey)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] font-medium text-slate-400 truncate">{{ $activeWorkout ? $activeWorkout->title : 'No Routine Prescribed' }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="setPortalTab('workouts')" class="px-2.5 py-1.5 rounded-[10px] bg-[#F5A524]/10 hover:bg-[#F5A524] text-[#F5A524] hover:text-neutral-950 border border-[#F5A524]/25 text-[11px] font-semibold transition-all cursor-pointer shrink-0">Start &rarr;</button>
                            </div>

                            @php
                                $quickExercises = $homeDayExercises->isNotEmpty() ? $homeDayExercises : ($activeWorkout?->exercises ?? collect());
                            @endphp
                            @if($activeWorkout && $quickExercises->isNotEmpty())
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    @foreach($quickExercises->take(4) as $ex)
                                        @php
                                            $qMatch = $exerciseVideoMatches[$ex->id] ?? null;
                                            $qVideoUrl = $ex->video_url ?: ($qMatch['video_url'] ?? null);
                                            $qDur = (int) ($ex->duration_seconds ?? 0);
                                            $qTarget = $ex->tracking_type === 'time'
                                                ? ($ex->sets . '×' . (($qDur >= 60 && $qDur % 60 === 0) ? ($qDur / 60) . 'm' : $qDur . 's'))
                                                : ($ex->sets . '×' . $ex->reps);
                                            $qReps = $ex->tracking_type === 'time'
                                                ? (($qDur >= 60 && $qDur % 60 === 0) ? ($qDur / 60) . ' min' : ($qDur > 0 ? $qDur . ' sec' : 'Timed'))
                                                : (string) ($ex->reps ?: '10-12');
                                            $qSub = $qMatch['category'] ?? ($ex->weight ? 'Load: ' . $ex->weight : (($ex->rest_seconds ?? 0) > 0 ? 'Rest ' . $ex->rest_seconds . 's' : 'Workout'));
                                        @endphp
                                        <div class="p-3 rounded-[10px] bg-[#0D0E10]/70 border border-[#26282D]/90 hover:border-[#F5A524]/30 transition-colors flex items-center justify-between gap-2">
                                            <div class="min-w-0 pr-1">
                                                <div class="text-xs font-semibold text-white truncate">{{ $ex->exercise_name }}</div>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[10px] text-slate-400 truncate">{{ $qSub }}</span>
                                                    @if($qVideoUrl)
                                                        <button type="button"
                                                                @click.stop="openExerciseVideo({{ Js::from([
                                                                    'title' => $ex->exercise_name,
                                                                    'url' => $qVideoUrl,
                                                                    'category' => $qMatch['category'] ?? 'Assigned Exercise',
                                                                    'equipment' => $qMatch['equipment'] ?? 'Form Guide',
                                                                    'notes' => $ex->notes ?: ($qMatch['notes'] ?? ''),
                                                                    'sets' => (string) ($ex->sets ?: '3'),
                                                                    'reps' => (string) $qReps,
                                                                    'trackingType' => $ex->tracking_type,
                                                                    'rest' => ($ex->rest_seconds ?? 0) > 0 ? $ex->rest_seconds . 's' : '',
                                                                    'weight' => $ex->weight ?: '',
                                                                    'setsReps' => $qTarget,
                                                                    'source' => $ex->video_url ? 'Assigned Video' : 'Video Library',
                                                                ]) }})"
                                                                title="Watch Form Video"
                                                                class="w-4 h-4 rounded-full bg-[#F5A524]/20 text-[#F5A524] hover:bg-[#F5A524] hover:text-neutral-950 flex items-center justify-center text-[8px] shrink-0 transition-colors cursor-pointer">
                                                            ▶
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-xs font-semibold text-[#F5A524] font-mono shrink-0">{{ $qTarget }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                @if($quickExercises->count() > 4)
                                    <button type="button" @click="setPortalTab('workouts')" class="w-full py-1.5 rounded-[10px] bg-[#0D0E10]/50 hover:bg-[#0D0E10] border border-[#26282D]/80 text-[11px] font-semibold text-[#F5A524] transition-colors cursor-pointer">
                                        +{{ $quickExercises->count() - 4 }} more exercises in today's split &rarr;
                                    </button>
                                @endif
                            @else
                                <div class="py-5 px-4 text-center rounded-[10px] bg-[#0D0E10]/50 border border-dashed border-[#26282D] space-y-1.5">
                                    <div class="text-xs font-semibold text-slate-300">No workout routine assigned yet</div>
                                    <p class="text-[11px] text-slate-400 max-w-sm mx-auto leading-relaxed">
                                        Contact your trainer to assign a personalized routine.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Today's Nutrition / Diet Quick Card -->
                    <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-5  space-y-3.5 flex flex-col justify-between">
                        <div class="space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center font-semibold text-base shrink-0 border border-[#F5A524]/30">
                                        <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18a9 9 0 0 1-18 0zm9-2V3m0 6 5-5M12 9 7 4"/></svg>
                                    </div>
                                    <div class="space-y-0.5">
                                        <h3 class="text-sm font-semibold text-white tracking-tight">Nutrition Target</h3>
                                        <p class="text-[11px] font-medium text-slate-400">{{ $activeDiet ? ($activeDiet->daily_calories . ' kcal goal') : 'Custom Nutrition' }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="setPortalTab('diets')" class="text-xs font-semibold text-[#F5A524] hover:underline cursor-pointer">Details &rarr;</button>
                            </div>

                            @if($activeDiet)
                                <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                                    <div class="p-3 rounded-[10px] bg-blue-500/10 border border-blue-500/20">
                                        <span class="text-[10px] font-semibold uppercase text-blue-400 tracking-wider">Protein</span>
                                        <div class="text-base font-semibold text-white mt-0.5 font-mono">{{ $activeDiet->protein_grams ?? 120 }}g</div>
                                    </div>
                                    <div class="p-3 rounded-[10px] bg-emerald-500/10 border border-emerald-500/20">
                                        <span class="text-[10px] font-semibold uppercase text-emerald-400 tracking-wider">Carbs</span>
                                        <div class="text-base font-semibold text-white mt-0.5 font-mono">{{ $activeDiet->carbs_grams ?? 200 }}g</div>
                                    </div>
                                    <div class="p-3 rounded-[10px] bg-rose-500/10 border border-rose-500/20">
                                        <span class="text-[10px] font-semibold uppercase text-rose-400 tracking-wider">Fats</span>
                                        <div class="text-base font-semibold text-white mt-0.5 font-mono">{{ $activeDiet->fat_grams ?? 60 }}g</div>
                                    </div>
                                </div>
                            @else
                                <div class="py-5 px-4 text-center rounded-[10px] bg-[#0D0E10]/50 border border-dashed border-[#26282D] space-y-1.5">
                                    <div class="text-xs font-semibold text-slate-300">No diet plan assigned yet</div>
                                    <p class="text-[11px] text-slate-400 max-w-sm mx-auto leading-relaxed">
                                        Ask your trainer for a customized meal plan.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>



                <!-- Assigned Locker (If assigned) -->
                @if($assignedLocker)
                    <div class="p-4 rounded-[10px] bg-[#141518] border border-[#26282D] flex items-center justify-between text-xs ">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center text-lg font-semibold border border-[#F5A524]/20"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></div>
                            <div>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-[#F5A524] block">Assigned Gym Locker</span>
                                <span class="font-semibold text-slate-900 dark:text-white font-mono text-base">Locker #{{ $assignedLocker->locker_number }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-[10px] text-[10px] font-semibold uppercase tracking-wider bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30">Active</span>
                    </div>
                @endif

                <!-- BODY MEASUREMENTS & TRANSFORMATION TRACKER -->
                <div x-data="{ showAllMeasurements: false }" class="rounded-[10px] bg-[#141518] border border-[#26282D] p-5 sm:p-6  space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center font-semibold text-lg shrink-0 border border-[#F5A524]/30">
                                ⚖️
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-semibold text-white tracking-tight truncate">Body Measurements &amp; Progress</h3>
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
                            <span class="px-2.5 py-1 rounded-[10px] text-[10px] font-semibold uppercase tracking-wider bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 shrink-0">
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

                        <!-- Latest Stats Primary Highlight Card (Athletic Stealth) -->
                        <div class="p-4 sm:p-5 rounded-[10px] bg-[#141518] border border-[#F5A524]/25 text-white space-y-4 shadow-none">
                            
                            <!-- Header of Latest Check-in -->
                            <div class="flex items-center justify-between text-xs pb-3 border-b border-white/10 flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 font-mono text-[11px] font-semibold">
                                        {{ \Carbon\Carbon::parse($latestMeasurement['date'])->format('d M Y') }}
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
                                    <div class="p-3 rounded-[10px] bg-white/5 border border-white/10 hover:border-[#F5A524]/40 transition-colors">
                                        <span class="text-[10px] uppercase font-semibold text-[#F5A524] tracking-wider block">Weight</span>
                                        <div class="text-lg sm:text-xl font-semibold text-white mt-0.5 font-mono">{{ $latestMeasurement['weight'] }} <span class="text-xs text-slate-400">kg</span></div>
                                        @if($weightDiff !== null)
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold mt-1 {{ $weightDiff <= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-[#F5A524]/20 text-[#F5A524]' }}">
                                                {{ $weightDiff <= 0 ? ($weightDiff . ' kg') : ('+' . $weightDiff . ' kg') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400 block mt-1 font-semibold">Bodyweight</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- 2. Body Fat -->
                                @if(!empty($latestMeasurement['body_fat']))
                                    <div class="p-3 rounded-[10px] bg-white/5 border border-white/10 hover:border-[#F5A524]/40 transition-colors">
                                        <span class="text-[10px] uppercase font-semibold text-[#F5A524] tracking-wider block">Body Fat</span>
                                        <div class="text-lg sm:text-xl font-semibold text-white mt-0.5 font-mono">{{ $latestMeasurement['body_fat'] }}%</div>
                                        @if($fatDiff !== null)
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold mt-1 {{ $fatDiff <= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                                {{ $fatDiff <= 0 ? ($fatDiff . '%') : ('+' . $fatDiff . '%') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400 block mt-1 font-semibold">Est. Fat %</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- 3. Waist -->
                                @if(!empty($latestMeasurement['waist']))
                                    <div class="p-3 rounded-[10px] bg-white/5 border border-white/10 hover:border-emerald-500/30 transition-colors">
                                        <span class="text-[10px] uppercase font-semibold text-emerald-400 tracking-wider block">Waist</span>
                                        <div class="text-lg sm:text-xl font-semibold text-white mt-0.5 font-mono">{{ $latestMeasurement['waist'] }} <span class="text-xs text-slate-400">in</span></div>
                                        @if($waistDiff !== null)
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold mt-1 {{ $waistDiff <= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                                {{ $waistDiff <= 0 ? ($waistDiff . ' in') : ('+' . $waistDiff . ' in') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400 block mt-1 font-semibold">Midsection</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- 4. Arms / Bicep -->
                                @if(!empty($latestMeasurement['bicep']))
                                    <div class="p-3 rounded-[10px] bg-white/5 border border-white/10 hover:border-[#F5A524]/30 transition-colors">
                                        <span class="text-[10px] uppercase font-semibold text-[#F5A524] tracking-wider block">Arms</span>
                                        <div class="text-lg sm:text-xl font-semibold text-white mt-0.5 font-mono">{{ $latestMeasurement['bicep'] }} <span class="text-xs text-slate-400">in</span></div>
                                        <span class="text-[10px] text-slate-400 block mt-1 font-semibold">Bicep Peak</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Secondary Tape Measurements Grid -->
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
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Tape Measurements</span>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2 text-center">
                                        @foreach($tapeMetrics as $tm)
                                            <div class="p-2 rounded-[10px] bg-white/5 border border-white/10">
                                                <span class="text-[10px] text-slate-400 uppercase font-semibold block">{{ $tm['label'] }}</span>
                                                <span class="text-xs font-semibold text-white font-mono block mt-0.5">{{ $tm['val'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Custom Metrics Chips -->
                            @if(!empty($latestMeasurement['custom_metrics']) && is_array($latestMeasurement['custom_metrics']))
                                @php
                                    $validCustom = array_filter($latestMeasurement['custom_metrics'], fn($c) => !empty($c['name']) && !empty($c['value']));
                                @endphp
                                @if(!empty($validCustom))
                                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                        @foreach($validCustom as $cm)
                                            <span class="px-2.5 py-1 rounded-[10px] bg-[#F5A524]/10 text-[#F5A524] border border-[#F5A524]/20 text-[10px] font-semibold">
                                                <span class="text-slate-400">{{ $cm['name'] }}:</span> <strong class="text-white">{{ $cm['value'] }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            <!-- Coach's Observation / Advice Quote -->
                            @if(!empty($latestMeasurement['notes']))
                                <div class="p-3 rounded-[10px] bg-[#F5A524]/10 border border-[#F5A524]/25 text-xs text-[#F5A524] leading-relaxed space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11a8 8 0 0 1-8 8H5l2-4a8 8 0 1 1 14-4z"/></svg></span>
                                        <span class="font-semibold uppercase tracking-wider text-[10px] text-[#F5A524]">Coach's Observation &amp; Feedback</span>
                                    </div>
                                    <p class="font-medium text-slate-200 pl-5 text-[11px]">{{ $latestMeasurement['notes'] }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Date-Wise Historical Logs Section -->
                        <div class="pt-1 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-white uppercase tracking-wider">Assessment History</span>
                                @if(count($measurements) > 0)
                                    <button type="button" 
                                            @click="showAllMeasurements = !showAllMeasurements" 
                                            class="text-xs font-semibold text-[#F5A524] hover:underline cursor-pointer flex items-center gap-1">
                                        <span x-text="showAllMeasurements ? 'Hide History &uarr;' : 'View All ({{ count($measurements) }} Logs) &rarr;'"></span>
                                    </button>
                                @endif
                            </div>

                            <!-- List of Measurements by Date -->
                            <div class="space-y-2" x-show="showAllMeasurements" x-cloak style="display: none;">
                                @foreach($measurements as $idx => $m)
                                    <div class="p-3.5 rounded-[10px] bg-[#0D0E10]/70 border border-[#26282D] text-xs space-y-2 transition-all">
                                        
                                        <!-- Log Header -->
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-lg bg-[#F5A524]/15 text-[#F5A524] font-semibold font-mono text-[10px] border border-[#F5A524]/25">
                                                    {{ \Carbon\Carbon::parse($m['date'] ?? now())->format('d M Y') }}
                                                </span>
                                                @if(!empty($m['weight']))
                                                    <span class="font-semibold text-white text-xs">⚖️ {{ $m['weight'] }} kg</span>
                                                @endif
                                                @if(!empty($m['body_fat']))
                                                    <span class="text-[#F5A524] font-semibold text-[10px] bg-[#F5A524]/15 border border-[#F5A524]/25 px-2 py-0.5 rounded-lg">{{ $m['body_fat'] }}% Fat</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] text-slate-400">
                                                By {{ $m['logged_by'] ?? 'Trainer' }}
                                            </span>
                                        </div>

                                        <!-- Tape Measurement Mini-Tiles Grid -->
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5 text-center">
                                            @if(!empty($m['waist']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Waist</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['waist'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['chest']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Chest</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['chest'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['bicep']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Arms</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['bicep'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['hips']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Hips</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['hips'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['thighs']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Thighs</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['thighs'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['shoulders']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Shoulders</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['shoulders'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['calves']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Calves</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['calves'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['neck']))
                                                <div class="p-2 rounded-[10px] bg-[#141518] border border-[#26282D]">
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider text-slate-400 block">Neck</span>
                                                    <span class="text-[11px] font-semibold text-white block mt-0.5">{{ $m['neck'] }}"</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Custom Metrics -->
                                        @if(!empty($m['custom_metrics']) && is_array($m['custom_metrics']))
                                            <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                                                @foreach($m['custom_metrics'] as $cm)
                                                    @if(!empty($cm['name']) && !empty($cm['value']))
                                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-[#F5A524]/10 text-[#F5A524] border border-[#F5A524]/20">{{ $cm['name'] }}: {{ $cm['value'] }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($m['notes']))
                                            <div class="p-2.5 rounded-[10px] bg-[#141518] border border-[#26282D] text-[11px] text-slate-300 italic flex items-start gap-2">
                                                <span class="text-xs shrink-0"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11a8 8 0 0 1-8 8H5l2-4a8 8 0 1 1 14-4z"/></svg></span>
                                                <span>&ldquo;{{ $m['notes'] }}&rdquo;</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-6 px-4 text-center rounded-[10px] bg-[#0D0E10]/50 border border-dashed border-[#26282D] space-y-1.5">
                            <div class="text-xs font-semibold text-slate-300">No body measurements recorded yet</div>
                            <p class="text-[11px] text-slate-400 max-w-sm mx-auto leading-relaxed">
                                Your personal trainer will record your weight, body fat %, and tape measurements date-wise during your coaching sessions.
                            </p>
                        </div>
                    @endif
                </div>

                @if($remainingAmt > 0)
                    <!-- Outstanding Due Reminder (Only shown when balance exists) -->
                    <div class="p-4 rounded-[10px] bg-amber-500/10 border border-amber-500/25 flex items-center justify-between text-xs text-amber-300 ">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">⚠️</span>
                            <div>
                                <span class="font-semibold text-white block">Fee Balance Outstanding</span>
                                <span class="text-[11px] text-amber-400">Total remaining: <strong class="text-white font-mono font-semibold">{{ $currency }}{{ number_format($remainingAmt, 2) }}</strong></span>
                            </div>
                        </div>
                        <button type="button" @click="setPortalTab('memberships')" class="px-3 py-1.5 rounded-[10px] bg-amber-400 text-neutral-950 font-semibold text-xs hover:bg-amber-300 transition-all cursor-pointer">
                            Pay / Bills &rarr;
                        </button>
                    </div>
                @endif

            </div>

            <!-- TAB 2: WORKOUT PLANS (CHAMPAGNE STEALTH) -->
            <div x-show="tab === 'workouts'" x-cloak class="space-y-5">
                <h1 class="text-2xl font-semibold text-white tracking-tight">Workout</h1>
                @if($member->workoutPlans->isNotEmpty())
                    @foreach($member->workoutPlans as $plan)
                        @php
                            $weekdayNames = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $workoutGroups = $plan->exercises->sortBy('sort_order')->groupBy(function ($exercise) use ($weekdayNames) {
                                $rawDay = trim((string) ($exercise->day ?? ''));
                                if ($rawDay === '') {
                                    return 'monday';
                                }
                                if (preg_match('/^day_([1-7])$/i', $rawDay, $matches)) {
                                    return $weekdayNames[(int) $matches[1] - 1];
                                }
                                return strtolower($rawDay);
                            });
                            $orderedWeekdays = array_values(array_filter($weekdayNames, static fn ($day) => $workoutGroups->has($day)));
                            $customDays = array_values(array_diff($workoutGroups->keys()->all(), $orderedWeekdays));
                            $workoutDays = array_values(array_merge($orderedWeekdays, $customDays));
                            $today = strtolower(now()->format('l'));
                            $firstDay = in_array($today, $workoutDays, true) ? $today : ($workoutDays[0] ?? 'monday');

                            $dayMeta = [];
                            foreach ($workoutDays as $dKey) {
                                $dList = $workoutGroups->get($dKey, collect());
                                $dSets = (int) $dList->sum('sets');
                                $dSeconds = 0;
                                foreach ($dList as $dEx) {
                                    $sets = max(1, (int) ($dEx->sets ?? 3));
                                    $workPerSet = $dEx->tracking_type === 'time' ? max(20, (int) ($dEx->duration_seconds ?? 45)) : 45;
                                    $restPerSet = max(15, (int) ($dEx->rest_seconds ?? 45));
                                    $dSeconds += $sets * ($workPerSet + $restPerSet);
                                }
                                $dayMeta[$dKey] = [
                                    'label' => ucwords(str_replace('_', ' ', $dKey)),
                                    'count' => $dList->count(),
                                    'sets' => $dSets,
                                    'estMinutes' => max(5, (int) ceil($dSeconds / 60)),
                                    'ids' => $dList->pluck('id')->map(static fn ($id) => (int) $id)->values()->all(),
                                ];
                            }
                            $storageKey = 'gym_workout_done_' . ($member->id ?? 0) . '_p' . $plan->id . '_' . now()->format('Y-m-d');
                        @endphp

                        <section x-data="{
                                    workoutDay: {{ Js::from($firstDay) }},
                                    todayKey: {{ Js::from($today) }},
                                    search: '',
                                    storageKey: {{ Js::from($storageKey) }},
                                    dayMeta: {{ Js::from($dayMeta) }},
                                    doneMap: {},
                                    restLeft: 0,
                                    restTotal: 0,
                                    restEnd: 0,
                                    restExerciseName: '',
                                    restInterval: null,
                                    init() {
                                        try {
                                            const saved = localStorage.getItem(this.storageKey);
                                            if (saved) {
                                                const parsed = JSON.parse(saved);
                                                if (parsed && typeof parsed === 'object') {
                                                    this.doneMap = parsed;
                                                }
                                            }
                                        } catch (e) {}
                                    },
                                    saveDone() {
                                        try {
                                            localStorage.setItem(this.storageKey, JSON.stringify(this.doneMap));
                                        } catch (e) {}
                                    },
                                    toggleDone(id) {
                                        const next = Object.assign({}, this.doneMap);
                                        if (next[id]) {
                                            delete next[id];
                                        } else {
                                            next[id] = true;
                                        }
                                        this.doneMap = next;
                                        this.saveDone();
                                    },
                                    isDone(id) {
                                        return !!this.doneMap[id];
                                    },
                                    completeAllDayDone() {
                                        const meta = this.dayMeta[this.workoutDay];
                                        if (!meta) return;
                                        const next = Object.assign({}, this.doneMap);
                                        (meta.ids || []).forEach((id) => { next[id] = true; });
                                        this.doneMap = next;
                                        this.saveDone();
                                    },
                                    clearDayDone() {
                                        const meta = this.dayMeta[this.workoutDay];
                                        if (!meta) return;
                                        const next = Object.assign({}, this.doneMap);
                                        (meta.ids || []).forEach((id) => { delete next[id]; });
                                        this.doneMap = next;
                                        this.saveDone();
                                    },
                                    dayDoneCount(dayKey) {
                                        const meta = this.dayMeta[dayKey];
                                        if (!meta || !meta.ids) return 0;
                                        return meta.ids.filter((id) => !!this.doneMap[id]).length;
                                    },
                                    get activeDayMeta() {
                                        return this.dayMeta[this.workoutDay] || { label: 'Workout', count: 0, sets: 0, estMinutes: 0, ids: [] };
                                    },
                                    get activeDayDoneCount() {
                                        return this.dayDoneCount(this.workoutDay);
                                    },
                                    get activeDayProgressPct() {
                                        const total = this.activeDayMeta.count || 0;
                                        return total > 0 ? Math.round((this.activeDayDoneCount / total) * 100) : 0;
                                    },
                                    startRest(seconds, exerciseTitle) {
                                        const sec = Number(seconds) || 0;
                                        if (sec <= 0) return;
                                        this.stopRest();
                                        this.restLeft = sec;
                                        this.restTotal = sec;
                                        this.restExerciseName = exerciseTitle || 'Rest Period';
                                        this.restEnd = Date.now() + sec * 1000;
                                        this.restInterval = setInterval(() => {
                                            this.restLeft = Math.max(0, Math.ceil((this.restEnd - Date.now()) / 1000));
                                            if (!this.restLeft) {
                                                if (navigator.vibrate) {
                                                    try { navigator.vibrate([120, 60, 120]); } catch (e) {}
                                                }
                                                this.stopRest();
                                            }
                                        }, 250);
                                    },
                                    adjustRest(delta) {
                                        if (this.restLeft <= 0) return;
                                        this.restEnd += delta * 1000;
                                        this.restTotal = Math.max(1, this.restTotal + delta);
                                        this.restLeft = Math.max(0, Math.ceil((this.restEnd - Date.now()) / 1000));
                                        if (!this.restLeft) this.stopRest();
                                    },
                                    stopRest() {
                                        if (this.restInterval) clearInterval(this.restInterval);
                                        this.restInterval = null;
                                        this.restLeft = 0;
                                        this.restTotal = 0;
                                        this.restExerciseName = '';
                                    },
                                    destroy() { this.stopRest(); }
                                }"
                                 x-effect="if (tab !== 'workouts') stopRest()"
                                 class="rounded-[10px] bg-[#141518] border border-[#26282D] shadow-none overflow-hidden">

                            <!-- Luxury Champagne Stealth Hero Header -->
                            <header class="relative overflow-hidden border-b border-[#26282D] border-t-2 border-t-[#F5A524] bg-[#141518] p-5 sm:p-6 space-y-5">
                                

                                <div class="relative flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 text-[10px] font-semibold uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 "></span>
                                        Active Training Plan
                                    </span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($plan->trainer)
                                            <span class="px-2.5 py-1 rounded-[10px] bg-[#0D0E10]/80 border border-[#26282D] text-[10px] font-semibold text-slate-300">
                                                Coach: <span class="text-white">{{ $plan->trainer->full_name ?? $plan->trainer->name }}</span>
                                            </span>
                                        @endif
                                        <span class="px-2.5 py-1 rounded-[10px] bg-[#0D0E10]/80 border border-neutral-700 text-[10px] font-semibold uppercase tracking-wider text-[#F5A524]">
                                            {{ ucfirst($plan->level ?? 'Custom') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="relative space-y-1.5">
                                    <h3 class="text-xl sm:text-2xl font-semibold tracking-tight text-white leading-tight break-words">{{ $plan->title }}</h3>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                        <span class="inline-flex items-center gap-1 text-slate-300 font-semibold">
                                            {{ ucwords(str_replace('_', ' ', $plan->goal ?? 'Strength & Conditioning')) }}
                                        </span>
                                        @if($plan->start_date)
                                            <span class="text-neutral-600">&bull;</span>
                                            <span>Started {{ \Carbon\Carbon::parse($plan->start_date)->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- 4-Tile Athletic KPI Strip -->
                                <div class="relative grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    <div class="rounded-[10px] bg-[#0D0E10]/85 border border-[#26282D]/90 p-3">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Days / Wk</div>
                                        <div class="mt-0.5 text-xl font-semibold text-white tabular-nums">{{ count($workoutDays) }}</div>
                                    </div>
                                    <div class="rounded-[10px] bg-[#0D0E10]/85 border border-[#26282D]/90 p-3">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Split Exercises</div>
                                        <div class="mt-0.5 text-xl font-semibold text-[#F5A524] tabular-nums" x-text="activeDayMeta.count">0</div>
                                    </div>
                                    <div class="rounded-[10px] bg-[#0D0E10]/85 border border-[#26282D]/90 p-3">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total Sets</div>
                                        <div class="mt-0.5 text-xl font-semibold text-white tabular-nums" x-text="activeDayMeta.sets">0</div>
                                    </div>
                                    <div class="rounded-[10px] bg-[#0D0E10]/85 border border-[#26282D]/90 p-3">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Est. Session</div>
                                        <div class="mt-0.5 text-xl font-semibold text-white tabular-nums" x-text="'~' + activeDayMeta.estMinutes + 'm'">~0m</div>
                                    </div>
                                </div>

                                <!-- Live Daily Workout Progress Bar -->
                                @if(count($workoutDays))
                                    <div class="relative rounded-[10px] bg-[#0D0E10]/75 border border-[#26282D] p-3.5 sm:p-4 space-y-2.5">
                                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-slate-200" x-text="activeDayMeta.label + ' Progress'"></span>
                                                <span class="font-semibold text-[#F5A524] tabular-nums" x-text="activeDayDoneCount + ' / ' + activeDayMeta.count + ' (' + activeDayProgressPct + '%)'"></span>
                                                <span x-show="activeDayProgressPct === 100" x-cloak class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-semibold">
                                                    ✓ All Done!
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                        x-show="activeDayMeta.count > 0 && activeDayDoneCount < activeDayMeta.count"
                                                        x-cloak
                                                        @click="completeAllDayDone()"
                                                        class="px-2.5 py-1 rounded-lg bg-emerald-500/15 hover:bg-emerald-500 text-emerald-300 hover:text-neutral-950 border border-emerald-500/30 text-[11px] font-semibold transition-colors cursor-pointer">
                                                    ✓ Complete All
                                                </button>
                                                <button type="button"
                                                        x-show="activeDayDoneCount > 0"
                                                        x-cloak
                                                        @click="clearDayDone()"
                                                        class="px-2 py-1 rounded-lg bg-neutral-800/80 hover:bg-neutral-700 text-[11px] font-semibold text-slate-300 hover:text-white cursor-pointer transition-colors">
                                                    Reset
                                                </button>
                                            </div>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-neutral-800 overflow-hidden">
                                            <div class="h-full rounded-full bg-[#F5A524] transition-all duration-300"
                                                 :style="'width: ' + activeDayProgressPct + '%'"></div>
                                        </div>
                                    </div>
                                @endif
                            </header>

                            <!-- Workout Body -->
                            <div class="p-4 sm:p-6 space-y-5 bg-[#12151C]">

                                <!-- Coach's Instructions Callout -->
                                @if(!empty($plan->notes))
                                    <div x-data="{ expandedNotes: false }" class="rounded-[10px] bg-[#F5A524]/5 border border-[#F5A524]/20 p-4 space-y-1.5">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[10px] font-semibold uppercase tracking-wide text-[#F5A524]">Coach's Notes &amp; Focus</span>
                                            @if(strlen($plan->notes) > 130)
                                                <button type="button" @click="expandedNotes = !expandedNotes" class="text-[10px] font-semibold text-[#F5A524] underline cursor-pointer" x-text="expandedNotes ? 'Show less' : 'Read full'"></button>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-300 leading-relaxed" :class="expandedNotes ? '' : 'line-clamp-3'">{{ $plan->notes }}</p>
                                    </div>
                                @endif

                                <!-- Live Rest Timer Countdown Banner -->
                                <div x-show="restLeft > 0" x-cloak class="rounded-[10px] border border-[#F5A524]/50 bg-[#141518] p-4 shadow-none space-y-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-semibold uppercase tracking-wide text-[#F5A524] block">⏱ Rest Timer Active</span>
                                            <p class="text-xs font-semibold text-slate-300 truncate mt-0.5" x-text="restExerciseName"></p>
                                        </div>
                                        <div class="text-3xl font-semibold tabular-nums font-mono text-white shrink-0" role="timer"
                                             x-text="Math.floor(restLeft / 60) + ':' + String(restLeft % 60).padStart(2, '0')"></div>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-neutral-800 overflow-hidden">
                                        <div class="h-full bg-[#F5A524] transition-all duration-200"
                                             :style="'width: ' + (restTotal > 0 ? Math.min(100, Math.round((restLeft / restTotal) * 100)) : 0) + '%'"></div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="adjustRest(-15)" class="px-3 py-1.5 rounded-[10px] bg-[#0D0E10] border border-neutral-700 text-xs font-semibold text-slate-200 hover:border-[#F5A524]/50 cursor-pointer">-15s</button>
                                        <button type="button" @click="adjustRest(15)" class="px-3 py-1.5 rounded-[10px] bg-[#0D0E10] border border-neutral-700 text-xs font-semibold text-slate-200 hover:border-[#F5A524]/50 cursor-pointer">+15s</button>
                                        <button type="button" @click="stopRest()" class="px-4 py-1.5 rounded-[10px] bg-[#F5A524] text-neutral-950 text-xs font-semibold hover:bg-[#E09414] cursor-pointer">Skip Rest</button>
                                    </div>
                                </div>

                                @if(count($workoutDays))
                                    <!-- Interactive Split Day Selector -->
                                    <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar" aria-label="Workout split day">
                                        @foreach($workoutDays as $day)
                                            @php $isTodayDay = ($day === $today); @endphp
                                            <button type="button"
                                                    @click="workoutDay = '{{ $day }}'; search = ''; stopRest()"
                                                    :aria-pressed="workoutDay === '{{ $day }}'"
                                                    :class="workoutDay === '{{ $day }}' ? 'bg-[#F5A524] text-neutral-950 border-[#F5A524] shadow-none ' : 'bg-[#141518] text-slate-300 border-[#26282D] hover:border-neutral-700'"
                                                    class="min-w-[88px] shrink-0 rounded-[10px] border px-3.5 py-2.5 text-center transition-all cursor-pointer relative">
                                                @if($isTodayDay)
                                                    <span :class="workoutDay === '{{ $day }}' ? 'bg-neutral-950 text-[#F5A524]' : 'bg-[#F5A524]/20 text-[#F5A524]'"
                                                          class="inline-block px-1.5 py-0.5 rounded-md text-[8px] font-semibold uppercase tracking-wide mb-1">Today</span>
                                                @endif
                                                <span class="block text-xs font-semibold uppercase tracking-wider">{{ strlen($day) <= 9 ? substr(ucfirst(str_replace('_', ' ', $day)), 0, 3) : ucwords(str_replace('_', ' ', $day)) }}</span>
                                                <span :class="workoutDay === '{{ $day }}' ? 'text-neutral-900/85 font-semibold' : (dayDoneCount('{{ $day }}') === {{ $workoutGroups[$day]->count() }} && {{ $workoutGroups[$day]->count() }} > 0 ? 'text-emerald-400 font-semibold' : 'text-slate-400')"
                                                      class="mt-0.5 block text-[10px] tabular-nums"
                                                      x-text="(dayDoneCount('{{ $day }}') === {{ $workoutGroups[$day]->count() }} && {{ $workoutGroups[$day]->count() }} > 0 ? '✓ ' : '') + dayDoneCount('{{ $day }}') + '/{{ $workoutGroups[$day]->count() }}'"></span>
                                            </button>
                                        @endforeach
                                    </div>

                                    <!-- Search Inside Selected Split -->
                                    <div class="flex items-center gap-2.5 rounded-[10px] border border-[#26282D] bg-[#0D0E10] px-3.5 focus-within:border-[#F5A524]/60 transition-colors">
                                        <svg aria-hidden="true" class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="10" cy="10" r="6" stroke-width="1.8"/><path d="m15 15 5 5" stroke-width="1.8"/></svg>
                                        <input type="search" aria-label="Search exercises in this split" x-model="search" placeholder="Search exercises in this split..." class="min-w-0 w-full bg-transparent py-2.5 text-xs text-white placeholder:text-slate-500 focus:outline-none">
                                        <button type="button" x-show="search.trim()" x-cloak @click="search = ''" class="text-xs text-slate-400 hover:text-white px-1 cursor-pointer">✕</button>
                                    </div>

                                    <!-- Day Exercise Cards -->
                                    @foreach($workoutDays as $day)
                                        <div x-show="workoutDay === '{{ $day }}'" x-cloak class="space-y-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <h4 class="text-xs font-semibold uppercase tracking-wide text-white">{{ ucwords(str_replace('_', ' ', $day)) }} Routine</h4>
                                                <span class="text-[11px] text-slate-400 font-medium">Tap check to track sets</span>
                                            </div>

                                            <div x-data="{ names: {{ Js::from($workoutGroups[$day]->pluck('exercise_name')->values()) }} }"
                                                 x-show="search.trim() && !names.some(name => name.toLowerCase().includes(search.trim().toLowerCase()))"
                                                 x-cloak
                                                 class="rounded-[10px] border border-dashed border-neutral-700 bg-[#141518]/50 p-6 text-center space-y-2">
                                                <p class="text-xs text-slate-400">No matching exercises found in {{ ucwords(str_replace('_', ' ', $day)) }}.</p>
                                                <button type="button" @click="search = ''" class="px-3 py-1.5 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] text-xs font-semibold cursor-pointer">Clear Search</button>
                                            </div>

                                            @foreach($workoutGroups[$day]->values() as $index => $ex)
                                                @php
                                                    $exId = (int) $ex->id;
                                                    $matchedLib = $exerciseVideoMatches[$exId] ?? null;
                                                    $resolvedVideo = $ex->video_url ?: ($matchedLib['video_url'] ?? null);
                                                    $resolvedNotes = $ex->notes ?: ($matchedLib['notes'] ?? null);
                                                    $dur = (int) ($ex->duration_seconds ?? 0);
                                                    $volumeValue = $ex->tracking_type === 'time'
                                                        ? ($dur > 0 ? (($dur >= 60 && $dur % 60 === 0) ? ($dur / 60) . ' min' : $dur . ' sec') : 'Timed')
                                                        : $ex->reps;
                                                    $videoPayload = $resolvedVideo ? [
                                                        'title' => $ex->exercise_name,
                                                        'url' => $resolvedVideo,
                                                        'category' => $matchedLib['category'] ?? ucwords(str_replace('_', ' ', $day)),
                                                        'equipment' => $matchedLib['equipment'] ?? ($ex->weight ? 'Load: ' . $ex->weight : 'Gym Exercise'),
                                                        'notes' => $resolvedNotes ?? '',
                                                        'sets' => (string) ($ex->sets ?: '3'),
                                                        'reps' => (string) ($volumeValue ?: '10-12'),
                                                        'trackingType' => $ex->tracking_type,
                                                        'rest' => ($ex->rest_seconds ?? 0) > 0 ? $ex->rest_seconds . 's' : '',
                                                        'weight' => $ex->weight ?: '',
                                                        'setsReps' => $ex->sets . ' Sets × ' . $volumeValue,
                                                        'source' => $ex->video_url ? 'Assigned Video' : 'Video Library',
                                                    ] : null;
                                                @endphp

                                                <article x-data="{ exerciseName: {{ Js::from($ex->exercise_name) }} }"
                                                         x-show="exerciseName.toLowerCase().includes(search.trim().toLowerCase())"
                                                         :class="isDone({{ $exId }}) ? 'border-emerald-500/40 bg-emerald-950/15' : 'border-[#26282D] bg-[#141518] hover:border-neutral-700'"
                                                         class="rounded-[10px] border p-4 sm:p-5 space-y-3.5 transition-all ">

                                                    <!-- Top Row: Done Toggle + Number + Title + Badges -->
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="flex items-start gap-3 min-w-0">
                                                            <button type="button"
                                                                    @click="toggleDone({{ $exId }})"
                                                                    :aria-pressed="isDone({{ $exId }})"
                                                                    title="Mark exercise complete"
                                                                    :class="isDone({{ $exId }}) ? 'bg-emerald-500 border-emerald-400 text-neutral-950 shadow-none shadow-emerald-500/20' : 'bg-[#0D0E10] border-neutral-700 text-slate-400 hover:border-[#F5A524]'"
                                                                    class="w-9 h-9 rounded-[10px] border flex items-center justify-center shrink-0 font-semibold text-sm transition-all cursor-pointer mt-0.5">
                                                                <span x-show="isDone({{ $exId }})" x-cloak>✓</span>
                                                                <span x-show="!isDone({{ $exId }})" class="font-mono text-xs text-[#F5A524]">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                                            </button>

                                                            <div class="min-w-0 space-y-1">
                                                                <h5 :class="isDone({{ $exId }}) ? 'line-through text-slate-400' : 'text-white'"
                                                                    class="text-sm sm:text-base font-semibold leading-snug break-words transition-colors">
                                                                    {{ $ex->exercise_name }}
                                                                </h5>
                                                                <div class="flex flex-wrap items-center gap-1.5">
                                                                    @if($matchedLib)
                                                                        <span class="px-2 py-0.5 rounded-md bg-[#0D0E10] border border-[#26282D] text-[10px] font-semibold text-slate-300">
                                                                            {{ $matchedLib['category'] }} &middot; {{ $matchedLib['equipment'] }}
                                                                        </span>
                                                                    @endif
                                                                    @if($ex->weight)
                                                                        <span class="px-2 py-0.5 rounded-md bg-[#F5A524]/10 border border-[#F5A524]/25 text-[10px] font-semibold text-[#F5A524]">
                                                                            {{ $ex->weight }}
                                                                        </span>
                                                                    @endif
                                                                    @if($resolvedVideo)
                                                                        <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/25 text-[10px] font-semibold text-emerald-300">
                                                                            Form video
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 3-Column Athletic Prescription Grid -->
                                                    <dl class="grid grid-cols-3 gap-2 rounded-[10px] border border-[#26282D]/90 bg-[#0D0E10] p-3">
                                                        <div class="text-center border-r border-[#26282D]/80 pr-2">
                                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $ex->tracking_type === 'time' ? 'Rounds' : 'Sets' }}</dt>
                                                            <dd class="mt-0.5 text-base font-semibold tabular-nums font-mono text-[#F5A524]">{{ $ex->sets }}</dd>
                                                        </div>
                                                        <div class="text-center border-r border-[#26282D]/80 px-2">
                                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $ex->tracking_type === 'time' ? 'Duration' : 'Reps' }}</dt>
                                                            <dd class="mt-0.5 text-base font-semibold tabular-nums font-mono text-white">{{ $volumeValue }}</dd>
                                                        </div>
                                                        <div class="text-center pl-2">
                                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Rest</dt>
                                                            <dd class="mt-0.5 text-base font-semibold tabular-nums font-mono text-slate-200">{{ ($ex->rest_seconds ?? 0) > 0 ? $ex->rest_seconds . 's' : '—' }}</dd>
                                                        </div>
                                                    </dl>

                                                    <!-- Balanced Action Buttons Row -->
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        @if(($ex->rest_seconds ?? 0) > 0)
                                                            <button type="button"
                                                                    @click="startRest({{ (int) $ex->rest_seconds }}, {{ Js::from($ex->exercise_name) }})"
                                                                    class="flex-1 min-w-[120px] py-2.5 px-3 rounded-[10px] bg-[#0D0E10] hover:bg-neutral-900 border border-[#F5A524]/30 text-xs font-semibold text-[#F5A524] flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                                                <span>⏱</span>
                                                                <span>Start {{ $ex->rest_seconds }}s Rest</span>
                                                            </button>
                                                        @endif

                                                        @if($videoPayload)
                                                            <button type="button"
                                                                    @click="openExerciseVideo({{ Js::from($videoPayload) }})"
                                                                    class="flex-1 min-w-[130px] py-2.5 px-3 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414] text-neutral-950 text-xs font-semibold shadow-none  flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                                                <span>▶</span>
                                                                <span>Watch Form Video</span>
                                                            </button>
                                                        @endif

                                                        <button type="button"
                                                                @click="toggleDone({{ $exId }})"
                                                                :aria-pressed="isDone({{ $exId }})"
                                                                :class="isDone({{ $exId }}) ? 'bg-emerald-500 text-neutral-950 border-emerald-400 shadow-none shadow-emerald-500/20' : 'bg-[#0D0E10] hover:bg-emerald-500/10 text-emerald-300 border-emerald-500/35'"
                                                                class="w-full sm:w-auto sm:min-w-[130px] py-2.5 px-3.5 rounded-[10px] border text-xs font-semibold flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                                            <span x-text="isDone({{ $exId }}) ? '✓ Completed' : '○ Mark Complete'"></span>
                                                        </button>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    @endforeach
                                @else
                                    <div class="py-8 text-center text-xs text-slate-400 rounded-[10px] border border-dashed border-[#26282D]">
                                        Your trainer has not added exercises to this plan yet.
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endforeach
                @else
                    <div class="p-10 rounded-[10px] bg-[#141518] border border-[#26282D] text-center space-y-3">
                        <div class="w-12 h-12 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center mx-auto text-xl border border-[#F5A524]/30">
                            <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9v6m3-8v10m12-10v10m3-8v6M6 12h12"/></svg>
                        </div>
                        <h4 class="text-base font-semibold text-white">No Workout Routine Assigned</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">Ask your gym trainer to prescribe a custom exercise routine suited to your fitness goals.</p>
                    </div>
                @endif
            </div>

            <!-- TAB 3: DIET & MEAL PLAN (REFINED PROFESSIONAL MEMBER INTERFACE) -->
            <div x-show="tab === 'diets'" x-cloak class="space-y-6">
                <h1 class="text-2xl font-semibold text-white tracking-tight">Nutrition</h1>
                @if($member->dietPlans->isNotEmpty())
                    @foreach($member->dietPlans as $diet)
                        @php
                            $targetCals = (int) ($diet->daily_calories ?? 2000);
                            $pGrams = (int) ($diet->protein_grams ?? 0);
                            $cGrams = (int) ($diet->carbs_grams ?? 0);
                            $fGrams = (int) ($diet->fat_grams ?? 0);

                            // Curated dish thumbnail lookup function for fitness meals
                            $resolveMealThumb = function($type, $name, $items) {
                                $haystack = strtolower($type . ' ' . $name . ' ' . $items);
                                if (str_contains($haystack, 'egg') || str_contains($haystack, 'omelet') || str_contains($haystack, 'breakfast') || str_contains($haystack, 'oat')) {
                                    return 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=240&auto=format&fit=crop&q=80';
                                }
                                if (str_contains($haystack, 'snack') || str_contains($haystack, 'fruit') || str_contains($haystack, 'apple') || str_contains($haystack, 'chana') || str_contains($haystack, 'nut')) {
                                    return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=240&auto=format&fit=crop&q=80';
                                }
                                if (str_contains($haystack, 'chicken') || str_contains($haystack, 'fish') || str_contains($haystack, 'meat') || str_contains($haystack, 'lunch') || str_contains($haystack, 'rice')) {
                                    return 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=240&auto=format&fit=crop&q=80';
                                }
                                if (str_contains($haystack, 'shake') || str_contains($haystack, 'whey') || str_contains($haystack, 'protein') || str_contains($haystack, 'post') || str_contains($haystack, 'smoothie')) {
                                    return 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?w=240&auto=format&fit=crop&q=80';
                                }
                                if (str_contains($haystack, 'dinner') || str_contains($haystack, 'paneer') || str_contains($haystack, 'roti') || str_contains($haystack, 'dal') || str_contains($haystack, 'tofu')) {
                                    return 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=240&auto=format&fit=crop&q=80';
                                }
                                return 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=240&auto=format&fit=crop&q=80';
                            };

                            // Prepare meal collection with parsed items
                            $preparedMeals = [];
                            foreach ($diet->meals as $meal) {
                                $rawItems = $meal->items_description ?: ($meal->items ?: '');
                                $normalized = str_replace(["\r\n", "\r"], "\n", $rawItems);
                                if (!str_contains($normalized, "\n")) {
                                    $withBreaks = preg_replace('/(\d(?:\.\d+)?g\s*[PCF]|\bkcal)\s*(?=[A-Z0-9•\-\*])/u', "$1\n", $normalized);
                                    if (str_contains($withBreaks, "\n")) {
                                        $normalized = $withBreaks;
                                    } elseif (!preg_match('/kcal|\bg\s*[PCF]\b/ui', $normalized)) {
                                        $normalized = str_replace(',', "\n", $normalized);
                                    }
                                }
                                $parsedItems = [];
                                foreach (explode("\n", $normalized) as $line) {
                                    $clean = trim((string) preg_replace('/^[•\-\*]\s*/u', '', trim($line)));
                                    if ($clean !== '') {
                                        $parts = explode(' — ', $clean, 2);
                                        $fullTitle = trim($parts[0]);
                                        $macros = isset($parts[1]) ? trim($parts[1]) : null;

                                        // Split primary name and portion if parenthesis exist
                                        if (preg_match('/^([^\(]+)\s*(\(.+\))$/u', $fullTitle, $matches)) {
                                            $foodName = trim($matches[1]);
                                            $portionDesc = trim($matches[2]);
                                        } else {
                                            $foodName = $fullTitle;
                                            $portionDesc = '';
                                        }

                                        $parsedItems[] = [
                                            'name' => $foodName,
                                            'portion' => $portionDesc,
                                            'full_title' => $fullTitle,
                                            'macros' => $macros,
                                        ];
                                    }
                                }

                                $mealTimeFormatted = null;
                                if (!empty($meal->recommended_time)) {
                                    try {
                                        $mealTimeFormatted = \Carbon\Carbon::parse($meal->recommended_time)->format('h:i A');
                                    } catch (\Throwable $e) {
                                        $mealTimeFormatted = substr((string)$meal->recommended_time, 0, 5);
                                    }
                                } elseif (!empty($meal->meal_time)) {
                                    $mealTimeFormatted = $meal->meal_time;
                                }

                                $mealTypeRaw = strtolower((string) ($meal->meal_type ?? ''));
                                $typeLabel = match(true) {
                                    str_contains($mealTypeRaw, 'breakfast') => 'Breakfast',
                                    str_contains($mealTypeRaw, 'morning') || str_contains($mealTypeRaw, 'snack_1') => 'Mid-Morning Snack',
                                    str_contains($mealTypeRaw, 'lunch') => 'Lunch',
                                    str_contains($mealTypeRaw, 'evening') || str_contains($mealTypeRaw, 'pre') => 'Evening Snack',
                                    str_contains($mealTypeRaw, 'post') => 'Post-Workout Fuel',
                                    str_contains($mealTypeRaw, 'dinner') => 'Dinner',
                                    str_contains($mealTypeRaw, 'bed') || str_contains($mealTypeRaw, 'night') => 'Bedtime Recovery',
                                    default => ucwords(str_replace('_', ' ', $meal->meal_type ?: 'Meal')),
                                };

                                // Single clean display title (avoids duplicate breakfast/breakfast)
                                $displayTitle = (!empty($meal->meal_name) && strtolower(trim($meal->meal_name)) !== strtolower(trim($typeLabel)))
                                    ? $meal->meal_name
                                    : $typeLabel;

                                $thumb = $resolveMealThumb($mealTypeRaw, (string)$meal->meal_name, $rawItems);

                                $preparedMeals[] = [
                                    'id' => $meal->id,
                                    'name' => $displayTitle,
                                    'type' => $typeLabel,
                                    'time' => $mealTimeFormatted,
                                    'calories' => (int) ($meal->calories ?? 0),
                                    'protein' => (int) ($meal->protein_grams ?? 0),
                                    'carbs' => (int) ($meal->carbs_grams ?? 0),
                                    'fat' => (int) ($meal->fat_grams ?? 0),
                                    'image' => $thumb,
                                    'items' => $parsedItems,
                                    'items_count' => count($parsedItems),
                                    'guidelines' => $diet->guidelines ?? '',
                                ];
                            }
                        @endphp

                        <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-4 sm:p-6 shadow-none space-y-6"
                             x-data="{
                                dateOffset: 0,
                                eatenMap: {},
                                activeMealModal: null,
                                planTargetCals: {{ $targetCals }},
                                planTargetP: {{ $pGrams }},
                                planTargetC: {{ $cGrams }},
                                planTargetF: {{ $fGrams }},
                                mealsList: {{ Js::from($preparedMeals) }},

                                init() {
                                    this.loadEatenState();
                                },

                                get currentDisplayDate() {
                                    const d = new Date();
                                    d.setDate(d.getDate() + this.dateOffset);
                                    if (this.dateOffset === 0) return 'Today, ' + d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
                                    if (this.dateOffset === -1) return 'Yesterday, ' + d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
                                    if (this.dateOffset === 1) return 'Tomorrow, ' + d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
                                    return d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short' });
                                },

                                get storageKey() {
                                    const d = new Date();
                                    d.setDate(d.getDate() + this.dateOffset);
                                    const dateKey = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                                    return 'gym_portal_eaten_{{ $member->id }}_' + dateKey;
                                },

                                loadEatenState() {
                                    try {
                                        const raw = localStorage.getItem(this.storageKey);
                                        this.eatenMap = raw ? JSON.parse(raw) : {};
                                    } catch (e) {
                                        this.eatenMap = {};
                                    }
                                },

                                saveEatenState() {
                                    try {
                                        localStorage.setItem(this.storageKey, JSON.stringify(this.eatenMap));
                                    } catch (e) {}
                                },

                                changeDay(delta) {
                                    this.dateOffset += delta;
                                    this.loadEatenState();
                                },

                                isEaten(mealId) {
                                    return !!this.eatenMap[mealId];
                                },

                                toggleEaten(mealId) {
                                    if (this.eatenMap[mealId]) {
                                        delete this.eatenMap[mealId];
                                    } else {
                                        this.eatenMap[mealId] = true;
                                    }
                                    this.saveEatenState();
                                },

                                get loggedTotals() {
                                    let cals = 0, p = 0, c = 0, f = 0, count = 0;
                                    this.mealsList.forEach(m => {
                                        if (this.isEaten(m.id)) {
                                            cals += m.calories || 0;
                                            p += m.protein || 0;
                                            c += m.carbs || 0;
                                            f += m.fat || 0;
                                            count++;
                                        }
                                    });
                                    const calsPct = this.planTargetCals > 0 ? Math.min(100, Math.round((cals / this.planTargetCals) * 100)) : 0;
                                    return { cals, p, c, f, count, calsPct };
                                },

                                openDetail(meal) {
                                    this.activeMealModal = meal;
                                },

                                closeDetail() {
                                    this.activeMealModal = null;
                                }
                             }">

                            <!-- 1. Header with Plan Title & Prescribed Badge -->
                            <div class="flex items-center justify-between gap-3 border-b border-[#26282D] pb-3 flex-wrap">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h3 class="text-xl sm:text-2xl font-semibold text-white tracking-tight truncate">{{ $diet->title }}</h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 shrink-0">
                                            Active Plan
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                                        <span>Daily Meal Schedule &amp; Target Nutrition</span>
                                        @if($diet->start_date)
                                            <span class="text-slate-600">•</span>
                                            <span>Since {{ \Carbon\Carbon::parse($diet->start_date)->format('d M') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- 2. Daily Nutrition Summary Card (Full Width, Balanced & Responsive) -->
                            <div class="p-4 sm:p-5 rounded-[10px] bg-[#0D0E10] border border-[#26282D] space-y-4 ">
                                <!-- Top row: Big Calories & Date Selector -->
                                <div class="flex items-start justify-between gap-3 flex-wrap">
                                    <div>
                                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">Daily Target Consumption</span>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-2xl sm:text-3xl font-semibold text-white font-mono leading-none tracking-tight">
                                                <span x-text="loggedTotals.cals.toLocaleString()"></span>
                                            </span>
                                            <span class="text-sm sm:text-base font-semibold text-slate-400 font-mono">
                                                / {{ number_format($targetCals) }} kcal
                                            </span>
                                        </div>
                                        <span class="text-xs text-[#F5A524] font-medium block mt-1" 
                                              x-text="loggedTotals.count === 0 ? '0 of ' + mealsList.length + ' meals logged today' : (loggedTotals.count + ' of ' + mealsList.length + ' meals logged (' + loggedTotals.calsPct + '% completed)')">
                                        </span>
                                    </div>

                                    <!-- Date Switcher Pill -->
                                    <div class="flex items-center gap-1 p-1 rounded-[10px] bg-[#141518] border border-[#26282D]  shrink-0">
                                        <button type="button" @click="changeDay(-1)" class="w-8 h-8 rounded-lg hover:bg-neutral-800 text-slate-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer" title="Previous Day">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                        </button>
                                        <span class="px-3 py-1 text-xs font-semibold font-mono text-white tracking-tight cursor-default" x-text="currentDisplayDate"></span>
                                        <button type="button" @click="changeDay(1)" class="w-8 h-8 rounded-lg hover:bg-neutral-800 text-slate-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer" title="Next Day">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Subtle Progress Bar -->
                                <div class="w-full bg-neutral-900 rounded-full h-2.5 overflow-hidden border border-[#26282D]">
                                    <div class="h-full rounded-full bg-[#F5A524] transition-all duration-300 "
                                         :style="'width: ' + loggedTotals.calsPct + '%'"></div>
                                </div>

                                <!-- Macro Grid: Equal Symmetrical 3-Column Responsive Fill -->
                                <div class="grid grid-cols-3 gap-2.5 sm:gap-3.5 pt-1">
                                    <!-- Protein -->
                                    <div class="p-3 sm:p-3.5 rounded-[10px] bg-blue-500/10 border border-blue-500/20 text-center">
                                        <span class="text-[10px] sm:text-xs font-semibold uppercase text-blue-400 tracking-wider block">Protein</span>
                                        <div class="text-base sm:text-xl font-semibold text-white font-mono mt-0.5">
                                            <span x-text="loggedTotals.p"></span><span class="text-xs text-blue-300/80 font-normal"> / {{ $pGrams }}g</span>
                                        </div>
                                        <span class="text-[10px] text-blue-300/70 block mt-0.5 font-medium">Muscle Growth</span>
                                    </div>

                                    <!-- Carbs -->
                                    <div class="p-3 sm:p-3.5 rounded-[10px] bg-emerald-500/10 border border-emerald-500/20 text-center">
                                        <span class="text-[10px] sm:text-xs font-semibold uppercase text-emerald-400 tracking-wider block">Carbs</span>
                                        <div class="text-base sm:text-xl font-semibold text-white font-mono mt-0.5">
                                            <span x-text="loggedTotals.c"></span><span class="text-xs text-emerald-300/80 font-normal"> / {{ $cGrams }}g</span>
                                        </div>
                                        <span class="text-[10px] text-emerald-300/70 block mt-0.5 font-medium">Energy Fuel</span>
                                    </div>

                                    <!-- Fats -->
                                    <div class="p-3 sm:p-3.5 rounded-[10px] bg-rose-500/10 border border-rose-500/20 text-center">
                                        <span class="text-[10px] sm:text-xs font-semibold uppercase text-rose-400 tracking-wider block">Fats</span>
                                        <div class="text-base sm:text-xl font-semibold text-white font-mono mt-0.5">
                                            <span x-text="loggedTotals.f"></span><span class="text-xs text-rose-300/80 font-normal"> / {{ $fGrams }}g</span>
                                        </div>
                                        <span class="text-[10px] text-rose-300/70 block mt-0.5 font-medium">Healthy Joints</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Refined Meal List (Clean, Bold, Perfectly Aligned) -->
                            <div class="space-y-3.5 pt-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                                        <span><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3v7a3 3 0 0 0 6 0V3M7 3v18m13-18c-3 2-4 6-4 10h4m0-10v18"/></svg></span>
                                        <span>Today's Meal Schedule</span>
                                    </h4>
                                    <span class="text-xs font-mono font-semibold text-slate-400">
                                        {{ count($preparedMeals) }} {{ count($preparedMeals) === 1 ? 'Meal' : 'Meals' }}
                                    </span>
                                </div>

                                @if(!empty($preparedMeals))
                                    <div class="space-y-3">
                                        @foreach($preparedMeals as $meal)
                                            <!-- Meal Card: Thumbnail + Clean Title & Time + Nutrition Strip + 44px Checkmark -->
                                            <div class="group rounded-[10px] bg-[#0D0E10] border border-[#26282D] hover:border-[#F5A524]/40 p-3.5 sm:p-4 transition-all  space-y-3 cursor-pointer"
                                                 :class="isEaten({{ $meal['id'] }}) ? 'border-emerald-500/50 bg-emerald-500/[0.03]' : ''"
                                                 @click="openDetail(mealsList.find(m => m.id === {{ $meal['id'] }}))">

                                                <!-- Top Row: Thumbnail + Single Title with Time + Checkmark Button -->
                                                <div class="flex items-center gap-3.5 sm:gap-4">
                                                    <!-- Food Thumbnail (56px) with rounded corners & fallback -->
                                                    <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-[10px] sm:rounded-[10px] overflow-hidden bg-[#141518] border border-[#26282D] shrink-0 ">
                                                        <img src="{{ $meal['image'] }}" 
                                                             alt="{{ $meal['name'] }}" 
                                                             loading="lazy"
                                                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                             onerror="this.onerror=null; this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                                        <div class="hidden w-full h-full flex items-center justify-center text-xl bg-[#141518]">
                                                            <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3v7a3 3 0 0 0 6 0V3M7 3v18m13-18c-3 2-4 6-4 10h4m0-10v18"/></svg>
                                                        </div>

                                                        <!-- Eaten Checkmark Overlay Badge -->
                                                        <div x-show="isEaten({{ $meal['id'] }})" x-cloak
                                                             class="absolute inset-0 bg-emerald-950/80 flex items-center justify-center text-white transition-opacity">
                                                            <div class="w-6 h-6 rounded-full bg-emerald-500 text-neutral-950 flex items-center justify-center text-xs font-semibold shadow-none">✓</div>
                                                        </div>
                                                    </div>

                                                    <!-- Center Column: Single Title + Inline Time + Food Items Preview -->
                                                    <div class="min-w-0 flex-1">
                                                        <!-- Single Meal Name with Time right next to it -->
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <h5 class="text-base sm:text-lg font-semibold text-white tracking-tight group-hover:text-[#F5A524] transition-colors leading-tight">
                                                                {{ $meal['name'] }}
                                                            </h5>
                                                            @if(!empty($meal['time']))
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#141518] border border-neutral-700/80 text-[11px] font-semibold font-mono text-[#F5A524] shrink-0">
                                                                    <svg class="w-3 h-3 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    {{ $meal['time'] }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <!-- Food Items Portion Preview -->
                                                        <p class="text-xs text-slate-400 mt-1 line-clamp-1 leading-relaxed">
                                                            {{ implode(' • ', array_map(fn($it) => $it['name'], array_slice($meal['items'], 0, 3))) }}
                                                            @if(count($meal['items']) > 3)
                                                                <span class="text-slate-500">+{{ count($meal['items']) - 3 }} more</span>
                                                            @endif
                                                        </p>
                                                    </div>

                                                    <!-- Separate 'Mark as eaten' action button (>=44px touch target) -->
                                                    <button type="button" 
                                                            @click.stop="toggleEaten({{ $meal['id'] }})"
                                                            :class="isEaten({{ $meal['id'] }}) 
                                                                ? 'bg-emerald-500 text-neutral-950 shadow-none shadow-emerald-500/25 ring-2 ring-emerald-400' 
                                                                : 'bg-[#141518] text-slate-400 hover:text-white border border-neutral-700/80 hover:border-neutral-500'"
                                                            class="w-11 h-11 rounded-[10px] flex items-center justify-center transition-all cursor-pointer shrink-0 ml-auto"
                                                            :title="isEaten({{ $meal['id'] }}) ? 'Marked as eaten (click to undo)' : 'Mark as eaten'">
                                                        <svg class="w-5 h-5 transition-transform" :class="isEaten({{ $meal['id'] }}) ? 'scale-110' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <!-- Bottom Nutrition Strip: BIG, PROMINENT, CLEARLY ALIGNED -->
                                                <div class="pt-2.5 border-t border-[#26282D]/80 flex items-center justify-between gap-3 text-xs flex-wrap">
                                                    <div class="flex items-center gap-3 sm:gap-4 font-mono flex-wrap">
                                                        <!-- Calories -->
                                                        <div class="flex items-center gap-1.5 text-white font-semibold text-sm sm:text-base">
                                                            <span class="text-amber-400 text-sm"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3c1 5 6 6 6 12a6 6 0 1 1-12 0c0-3 2-5 4-7 0 3 1 4 2 4 2-3 1-6 0-9z"/></svg></span>
                                                            <span>{{ number_format($meal['calories']) }}</span>
                                                            <span class="text-[11px] text-amber-400 font-semibold">kcal</span>
                                                        </div>

                                                        <span class="text-neutral-700 hidden sm:inline">•</span>

                                                        <!-- Protein -->
                                                        <div class="flex items-center gap-1 text-xs sm:text-sm font-semibold">
                                                            <span class="text-slate-400 text-[11px]">Prot:</span>
                                                            <span class="text-blue-400 font-mono font-semibold">{{ $meal['protein'] }}g</span>
                                                        </div>

                                                        <span class="text-neutral-700 hidden sm:inline">•</span>

                                                        <!-- Carbs -->
                                                        <div class="flex items-center gap-1 text-xs sm:text-sm font-semibold">
                                                            <span class="text-slate-400 text-[11px]">Carb:</span>
                                                            <span class="text-emerald-400 font-mono font-semibold">{{ $meal['carbs'] }}g</span>
                                                        </div>

                                                        <span class="text-neutral-700 hidden sm:inline">•</span>

                                                        <!-- Fats -->
                                                        <div class="flex items-center gap-1 text-xs sm:text-sm font-semibold">
                                                            <span class="text-slate-400 text-[11px]">Fat:</span>
                                                            <span class="text-rose-400 font-mono font-semibold">{{ $meal['fat'] }}g</span>
                                                        </div>
                                                    </div>

                                                    <span class="text-[11px] text-[#F5A524] font-semibold group-hover:underline flex items-center gap-1">
                                                        <span>Details</span>
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="py-8 text-center text-xs text-slate-400 bg-[#0D0E10]/50 rounded-[10px] border border-dashed border-[#26282D]">
                                        No meals scheduled in this plan yet.
                                    </div>
                                @endif
                            </div>

                            <!-- 4. Guidelines / Advice from Trainer or Nutritionist -->
                            @if(!empty($diet->guidelines))
                                <div class="p-4 sm:p-5 rounded-[10px] bg-[#F5A524]/5 border border-[#F5A524]/20 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6m-5 3h4M8 14a6 6 0 1 1 8 0l-1 2H9z"/></svg></span>
                                        <span class="font-semibold uppercase tracking-wider text-xs text-[#F5A524]">Notes from Trainer / Nutritionist:</span>
                                    </div>
                                    <p class="text-xs text-slate-300 leading-relaxed pl-6">
                                        {{ $diet->guidelines }}
                                    </p>
                                </div>
                            @endif

                            <!-- 5. Accessible Meal Details Bottom Sheet / Modal (Refined Professional UI/UX) -->
                            <div x-show="activeMealModal" x-cloak 
                                 class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/85 transition-opacity"
                                 @keydown.escape.window="closeDetail()">
                                
                                <div class="w-full max-w-lg rounded-t-3xl sm:rounded-[10px] bg-[#13161C] border border-[#26282D]/90 shadow-none flex flex-col max-h-[92vh] overflow-hidden"
                                     @click.away="closeDetail()">
                                    
                                    <!-- Mobile Drag Handle Indicator -->
                                    <div class="pt-3 pb-1 flex justify-center sm:hidden shrink-0">
                                        <div class="w-10 h-1 rounded-full bg-neutral-700/80"></div>
                                    </div>

                                    <!-- Scrollable Modal Content -->
                                    <div class="p-5 sm:p-6 overflow-y-auto space-y-5">
                                        
                                        <!-- Header: Photo Thumbnail, Title & Timing -->
                                        <div class="flex items-start justify-between gap-3 pb-4 border-b border-[#26282D]/80">
                                            <div class="flex items-center gap-3.5 min-w-0">
                                                <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-[10px] overflow-hidden bg-[#0D0E10] border border-[#26282D] shrink-0 shadow-none relative">
                                                    <img :src="activeMealModal?.image" 
                                                         :alt="activeMealModal?.name" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <h3 class="text-lg sm:text-xl font-semibold text-white tracking-tight leading-tight truncate" x-text="activeMealModal?.name"></h3>
                                                        <template x-if="activeMealModal?.time">
                                                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold font-mono bg-[#1A1E26] text-[#F5A524] border border-neutral-700/80 shrink-0 flex items-center gap-1">
                                                                <svg class="w-3 h-3 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                <span x-text="activeMealModal?.time"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <span class="text-xs text-slate-400 mt-1 block font-medium" x-text="activeMealModal?.type"></span>
                                                </div>
                                            </div>

                                            <button type="button" @click="closeDetail()" class="w-9 h-9 rounded-[10px] text-slate-400 hover:text-white hover:bg-neutral-800 flex items-center justify-center transition-colors cursor-pointer shrink-0" title="Close">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>

                                        <!-- Total Nutrition Cards (Balanced 4-column responsive grid) -->
                                        <div class="grid grid-cols-4 gap-2 text-center">
                                            <div class="p-3 rounded-[10px] bg-[#0D0E10] border border-[#26282D]">
                                                <span class="text-[10px] font-semibold text-amber-400 block uppercase tracking-wider">Calories</span>
                                                <span class="text-base sm:text-lg font-semibold text-white font-mono block mt-1" x-text="activeMealModal?.calories ? activeMealModal.calories + ' kcal' : '—'"></span>
                                            </div>
                                            <div class="p-3 rounded-[10px] bg-[#0D0E10] border border-[#26282D]">
                                                <span class="text-[10px] font-semibold text-blue-400 block uppercase tracking-wider">Protein</span>
                                                <span class="text-base sm:text-lg font-semibold text-white font-mono block mt-1" x-text="activeMealModal?.protein ? activeMealModal.protein + 'g' : '—'"></span>
                                            </div>
                                            <div class="p-3 rounded-[10px] bg-[#0D0E10] border border-[#26282D]">
                                                <span class="text-[10px] font-semibold text-emerald-400 block uppercase tracking-wider">Carbs</span>
                                                <span class="text-base sm:text-lg font-semibold text-white font-mono block mt-1" x-text="activeMealModal?.carbs ? activeMealModal.carbs + 'g' : '—'"></span>
                                            </div>
                                            <div class="p-3 rounded-[10px] bg-[#0D0E10] border border-[#26282D]">
                                                <span class="text-[10px] font-semibold text-rose-400 block uppercase tracking-wider">Fats</span>
                                                <span class="text-base sm:text-lg font-semibold text-white font-mono block mt-1" x-text="activeMealModal?.fat ? activeMealModal.fat + 'g' : '—'"></span>
                                            </div>
                                        </div>

                                        <!-- Food Items & Portions Section -->
                                        <div class="space-y-3 pt-1">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                                                    <span><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18a9 9 0 0 1-18 0zm9-2V3m0 6 5-5M12 9 7 4"/></svg></span>
                                                    <span>Food Items &amp; Portions</span>
                                                </h4>
                                                <span class="text-xs font-mono font-semibold text-[#F5A524]" x-text="(activeMealModal?.items?.length || 0) + ' items'"></span>
                                            </div>

                                            <!-- Item Cards with High-End Layout -->
                                            <div class="space-y-2">
                                                <template x-for="(food, fIdx) in (activeMealModal?.items || [])" :key="fIdx">
                                                    <div class="p-3 sm:p-3.5 rounded-[10px] bg-[#0D0E10] border border-[#26282D]/90 hover:border-neutral-700 transition-all flex items-center justify-between gap-3 ">
                                                        <div class="flex items-start gap-3 min-w-0 flex-1">
                                                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-semibold shrink-0 mt-0.5 ">✓</span>
                                                            <div class="min-w-0 flex-1">
                                                                <span class="font-semibold text-sm text-white block leading-snug" x-text="food.name"></span>
                                                                <template x-if="food.portion">
                                                                    <span class="text-xs text-slate-400 block mt-0.5 font-medium leading-relaxed" x-text="food.portion"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        
                                                        <template x-if="food.macros">
                                                            <div class="px-2.5 py-1 rounded-[10px] bg-[#1A1E26] border border-[#26282D] text-right shrink-0">
                                                                <span class="text-xs font-mono font-semibold text-[#F5A524] block" x-text="food.macros"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="!activeMealModal?.items || activeMealModal?.items.length === 0">
                                                    <div class="text-xs text-slate-400 italic py-4 text-center bg-[#0D0E10] rounded-[10px] border border-[#26282D]">
                                                        No specific ingredients listed.
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Content Beneath Food Items: Preparation Notes & Trainer Advice -->
                                        <div class="space-y-3 pt-2 border-t border-[#26282D]/80">
                                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                                                <span><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6m-5 3h4M8 14a6 6 0 1 1 8 0l-1 2H9z"/></svg></span>
                                                <span>Preparation &amp; Trainer Advice</span>
                                            </h4>

                                            <div class="p-4 rounded-[10px] bg-[#0D0E10] border border-[#F5A524]/20 space-y-2.5">
                                                <div class="flex items-start gap-3">
                                                    <span class="w-8 h-8 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center text-sm shrink-0 border border-[#F5A524]/30 font-semibold">
                                                        PT
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <span class="text-xs font-semibold text-white block">Certified Coach Guidance</span>
                                                        <p class="text-xs text-slate-300 mt-1 leading-relaxed" 
                                                           x-text="activeMealModal?.guidelines ? activeMealModal.guidelines : 'Cook with minimal processed oils. Ensure proper hydration with 400–500ml water before or after eating to maximize nutrient absorption and lean muscle recovery.'">
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Hydration Pill Strip -->
                                                <div class="pt-2 border-t border-[#26282D]/80 flex items-center gap-2 text-[11px] text-sky-300">
                                                    <span><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3c4 5 6 8 6 11a6 6 0 0 1-12 0c0-3 2-6 6-11z"/></svg></span>
                                                    <span>Keep at least 20 minutes gap between meal and cold water intake.</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Sticky Bottom Action Footer -->
                                    <div class="p-4 sm:p-5 bg-[#0D0E10] border-t border-[#26282D] flex items-center gap-3 shrink-0">
                                        <button type="button" 
                                                @click="toggleEaten(activeMealModal.id)"
                                                :class="isEaten(activeMealModal?.id) 
                                                    ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-500/25 ring-2 ring-emerald-400' 
                                                    : 'bg-[#F5A524] hover:bg-[#d6ba93] text-neutral-950 '"
                                                class="flex-1 py-3.5 px-5 rounded-[10px] text-xs sm:text-sm font-semibold transition-all flex items-center justify-center gap-2 cursor-pointer shadow-none">
                                            <template x-if="isEaten(activeMealModal?.id)">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>✓ Completed Today (Tap to Undo)</span>
                                                </span>
                                            </template>
                                            <template x-if="!isEaten(activeMealModal?.id)">
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Mark as Eaten Today</span>
                                                </span>
                                            </template>
                                        </button>
                                        <button type="button" @click="closeDetail()" class="py-3.5 px-5 rounded-[10px] bg-neutral-800 hover:bg-neutral-700 text-white text-xs sm:text-sm font-semibold transition-colors cursor-pointer shrink-0">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-12 rounded-[10px] bg-[#141518] border border-[#26282D] text-center space-y-3">
                        <div class="w-14 h-14 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center mx-auto text-2xl border border-[#F5A524]/30 shadow-none">
                            <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18a9 9 0 0 1-18 0zm9-2V3m0 6 5-5M12 9 7 4"/></svg>
                        </div>
                        <h4 class="text-lg font-semibold text-white">No Diet Plan Assigned</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                            Ask your gym trainer or certified nutritionist to create and assign a tailored meal schedule suited to your transformation goals.
                        </p>
                    </div>
                @endif
            </div>
            <!-- TAB 4: PERSONAL TRAINING & DEDICATED COACH -->
            <div x-show="tab === 'pt'" x-cloak class="space-y-5">
                <h1 class="text-2xl font-semibold text-white tracking-tight">Coaching</h1>
                @if($activePt)
                    @php
                        $ptUsed = (int) ($activePt->used_sessions ?? 0);
                        $ptTotal = (int) ($activePt->total_sessions ?? 1);
                        $ptRemaining = max(0, (int) ($activePt->remaining_sessions ?? ($ptTotal - $ptUsed)));
                        $ptPct = min(100, round(($ptUsed / max(1, $ptTotal)) * 100));
                        $coachName = $activePt->trainer?->full_name ?? ($member->assignedTrainer?->full_name ?? 'Personal Fitness Coach');

                        // Dynamic 100% Ratio Color System for PT Sessions (Green >= 75%, Yellow 40-74%, Red < 40%)
                        if ($ptPct >= 75) {
                            $ptBarGrad = 'from-emerald-500 via-green-400 to-teal-400';
                            $ptBarShadow = 'shadow-emerald-500/25 shadow-none';
                            $ptTextColor = 'text-emerald-400';
                        } elseif ($ptPct >= 40) {
                            $ptBarGrad = 'from-amber-500 via-yellow-400 to-amber-300';
                            $ptBarShadow = 'shadow-amber-500/25 shadow-none';
                            $ptTextColor = 'text-yellow-400';
                        } else {
                            $ptBarGrad = 'from-rose-600 via-rose-500 to-red-400';
                            $ptBarShadow = 'shadow-rose-500/25 shadow-none';
                            $ptTextColor = 'text-rose-400';
                        }
                    @endphp

                    <!-- Coach Spotlight Card -->
                    <div class="rounded-[10px] bg-[#141518] border border-[#F5A524]/35 p-5 sm:p-7 shadow-none space-y-5 text-white relative overflow-hidden">
                        

                        <!-- Top Row: Badge & Expiry -->
                        <div class="relative z-10 flex items-center justify-between gap-3 flex-wrap">
                            <span class="px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 flex items-center gap-1.5 ">
                                <span class="w-2 h-2 rounded-full bg-[#F5A524] "></span>
                                <span>Personal Training</span>
                            </span>
                            @if($activePt->end_date)
                                <span class="text-xs text-slate-400">Package Valid till <strong class="text-white">{{ $activePt->end_date->format('d M, Y') }}</strong></span>
                            @endif
                        </div>

                        <!-- Coach Profile & Remaining Sessions -->
                        <div class="relative z-10 flex items-center justify-between gap-4 pt-1 flex-wrap sm:flex-nowrap">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="relative shrink-0">
                                    <div class="w-14 h-14 rounded-[10px] bg-[#F5A524] text-neutral-950 font-semibold text-xl flex items-center justify-center shadow-none  border border-white/10">
                                        {{ strtoupper(substr($coachName, 0, 1)) }}
                                    </div>
                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-[#141518] flex items-center justify-center text-[10px] text-neutral-950 font-semibold">✓</span>
                                </div>
                                <div class="min-w-0 space-y-0.5">
                                    <h3 class="text-lg sm:text-xl font-semibold text-white tracking-tight truncate">{{ $coachName }}</h3>
                                    <p class="text-xs text-[#F5A524] font-semibold">{{ $activePt->package_name ?? 'Personal Training Package' }}</p>
                                    <p class="text-[11px] text-slate-400">Dedicated Personal Trainer &amp; Transformation Coach</p>
                                </div>
                            </div>

                            <!-- Sessions Available Dial -->
                            <div class="text-right shrink-0 p-3.5 rounded-[10px] bg-[#0D0E10]/90 border border-[#26282D] min-w-[120px]">
                                <div class="text-3xl sm:text-4xl font-semibold text-[#F5A524] font-mono leading-none">{{ $ptRemaining }}</div>
                                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-1">Sessions Available</div>
                            </div>
                        </div>

                        <!-- Progress Bar & Breakdown with 100% Ratio Zones -->
                        <div class="relative z-10 space-y-2 pt-2 border-t border-[#26282D]">
                            <div class="relative w-full bg-[#0d1017] rounded-full h-3.5 p-0.5 overflow-hidden border border-[#26282D] ">
                                <!-- 100% Ratio Track Zone Marks (0-40% Red, 40-75% Yellow, 75-100% Green) -->
                                <div class="absolute inset-0 flex pointer-events-none opacity-30">
                                    <div class="w-[40%] h-full border-r border-rose-500/30 bg-rose-500/5"></div>
                                    <div class="w-[35%] h-full border-r border-amber-500/30 bg-amber-500/5"></div>
                                    <div class="w-[25%] h-full bg-emerald-500/5"></div>
                                </div>
                                <div class="relative z-10 h-full rounded-full bg-[#F5A524] transition-all duration-500" style="width: {{ $ptPct }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-300">
                                <span>Used: <strong class="text-white font-mono">{{ $ptUsed }}</strong> of {{ $ptTotal }} Sessions (<span class="{{ $ptTextColor }} font-semibold font-mono">{{ $ptPct }}%</span>)</span>
                                <span class="{{ $ptTextColor }} font-semibold">{{ $ptRemaining }} Sessions Available</span>
                            </div>
                        </div>

                        <!-- Instant Session Verify Button CTA -->
                        <div class="relative z-10 pt-1">
                            <button type="button" 
                                    @click="startPortalScanner('code')" 
                                    class="w-full py-3.5 px-4 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414]  text-neutral-950 font-semibold text-xs sm:text-sm transition-all flex items-center justify-center gap-2 shadow-none  cursor-pointer">
                                <span>Verify session</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <!-- Upcoming Scheduled PT Sessions (Booked by Coach) -->
                    @if(isset($upcomingPtSessions) && $upcomingPtSessions->isNotEmpty())
                        <div class="rounded-[10px] bg-[#141518] border border-[#F5A524]/30 p-5 sm:p-6  space-y-4">
                            <div class="flex items-center justify-between border-b border-[#26282D] pb-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center text-lg font-semibold shrink-0 border border-[#F5A524]/30">
                                        <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-semibold text-white">Upcoming Scheduled Sessions</h4>
                                        <p class="text-xs text-slate-400 mt-0.5">PT training slots booked by your coach</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-[10px] text-xs font-semibold bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30">
                                    {{ $upcomingPtSessions->count() }} Upcoming
                                </span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach($upcomingPtSessions as $upSess)
                                    <div class="p-3.5 rounded-[10px] bg-[#0D0E10]/80 border border-[#26282D] flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-11 h-11 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 flex flex-col items-center justify-center shrink-0">
                                                <span class="text-[10px] font-semibold uppercase leading-none">{{ $upSess->session_date->format('M') }}</span>
                                                <span class="text-sm font-semibold leading-none mt-0.5">{{ $upSess->session_date->format('d') }}</span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-semibold text-white truncate">
                                                        {{ $upSess->session_date->isToday() ? 'Today' : ($upSess->session_date->isTomorrow() ? 'Tomorrow' : $upSess->session_date->format('l, d M')) }}
                                                    </span>
                                                    <span class="px-1.5 py-0.5 rounded bg-sky-500/15 text-sky-400 border border-sky-500/30 text-[10px] font-semibold uppercase">Booked</span>
                                                </div>
                                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                                    {{ \Carbon\Carbon::parse($upSess->start_time)->format('h:i A') }} &bull; {{ $upSess->duration_minutes ?? 60 }} mins &bull; {{ $upSess->focus_area ?? 'PT Workout' }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-semibold text-[#F5A524] shrink-0">{{ $upSess->trainer?->full_name ?? 'Coach' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Verified PT Sessions Timeline -->
                    <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-5 sm:p-6  space-y-4">
                        <div class="flex items-center justify-between border-b border-[#26282D] pb-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center text-lg font-semibold shrink-0 border border-[#F5A524]/30">
                                    <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6m-6 4h6m-6 4h4"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-white">Verified Sessions History</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Date-wise coaching workouts logged and confirmed with your trainer</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-[10px] text-xs font-semibold bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30">
                                {{ $ptSessionsHistory->count() }} {{ Str::plural('Session', $ptSessionsHistory->count()) }}
                            </span>
                        </div>

                        @if($ptSessionsHistory->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($ptSessionsHistory as $session)
                                    <div class="p-4 rounded-[10px] bg-[#0D0E10]/70 border border-[#26282D] hover:border-[#F5A524]/40 transition-all space-y-3">
                                        <!-- Top Row: Date, Time & Verified Badge -->
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-10 h-10 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 flex flex-col items-center justify-center shrink-0">
                                                    <span class="text-[10px] font-semibold uppercase leading-none">{{ $session->session_date ? $session->session_date->format('M') : $session->created_at->format('M') }}</span>
                                                    <span class="text-sm font-semibold leading-none mt-0.5">{{ $session->session_date ? $session->session_date->format('d') : $session->created_at->format('d') }}</span>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-white">
                                                        {{ $session->session_date ? $session->session_date->format('l, d M Y') : $session->created_at->format('l, d M Y') }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center gap-1.5 flex-wrap">
                                                        <span>{{ $session->confirmed_at ? $session->confirmed_at->format('h:i A') : ($session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('h:i A') : 'Recorded') }}</span>
                                                        <span class="text-neutral-600">•</span>
                                                        <span class="px-2 py-0.5 rounded bg-neutral-800 text-slate-300 font-sans text-[10px] font-medium">{{ $session->duration_minutes ?? 60 }} mins</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 flex items-center gap-1 shrink-0">
                                                <span>✓</span>
                                                <span>Confirmed</span>
                                            </span>
                                        </div>

                                        <!-- Middle Row: Coach & Workout Focus -->
                                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#26282D] text-xs">
                                            <div class="space-y-0.5">
                                                <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Coach</span>
                                                <div class="font-semibold text-[#F5A524] flex items-center gap-1.5 truncate">
                                                    <div class="w-4 h-4 rounded-full bg-[#F5A524]/20 text-[#F5A524] text-[10px] font-semibold flex items-center justify-center shrink-0">
                                                        {{ substr($session->trainer?->first_name ?? 'C', 0, 1) }}
                                                    </div>
                                                    <span class="truncate">{{ $session->trainer?->full_name ?? 'Assigned Coach' }}</span>
                                                </div>
                                            </div>
                                            <div class="space-y-0.5">
                                                <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Focus Area</span>
                                                <div class="font-semibold text-slate-200 truncate">
                                                    {{ $session->focus_area ?? 'Strength Training' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bottom Row: Package & Star Rating -->
                                        <div class="flex items-center justify-between text-xs pt-2 border-t border-[#26282D] flex-wrap gap-2">
                                            <div class="text-[11px] text-slate-400 truncate max-w-[200px]">
                                                <span class="text-slate-500">Plan:</span>
                                                <span class="font-semibold text-slate-300">{{ $session->memberPtPackage?->package_name ?? 'Personal Training' }}</span>
                                            </div>
                                            @if($session->rating)
                                                <div class="flex items-center gap-1 shrink-0">
                                                    <div class="flex text-[#F5A524] text-xs">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <span>{{ $i <= $session->rating ? '★' : '☆' }}</span>
                                                        @endfor
                                                    </div>
                                                    <span class="text-[10px] font-semibold text-[#F5A524] ml-0.5">{{ $session->rating }}/5</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Member Feedback Quote (if present) -->
                                        @if(!empty($session->member_feedback))
                                            <div class="p-2.5 rounded-[10px] bg-[#141518] border border-[#26282D] text-[11px] text-slate-300 italic flex items-start gap-1.5">
                                                <span class="text-[#F5A524] text-xs shrink-0">“</span>
                                                <span class="flex-1">{{ $session->member_feedback }}</span>
                                                <span class="text-[#F5A524] text-xs shrink-0">”</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-8 text-center text-xs text-slate-400 bg-[#0D0E10]/50 rounded-[10px] space-y-2">
                                <div class="text-3xl"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m13 2-9 12h7l-1 8 10-12h-7z"/></svg></div>
                                <p class="font-semibold text-white text-sm">No PT sessions confirmed yet</p>
                                <p class="text-[11px] text-slate-400 max-w-xs mx-auto leading-relaxed">When your coach logs a workout and you confirm via PIN or QR, your complete history will show here.</p>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- No Active PT Upgrade Screen -->
                    <div class="p-8 sm:p-12 rounded-[10px] bg-[#141518] border border-[#26282D] text-center space-y-4 shadow-none">
                        <div class="w-16 h-16 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] flex items-center justify-center mx-auto text-3xl border border-[#F5A524]/30 shadow-none">
                            <svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9v6m3-8v10m12-10v10m3-8v6M6 12h12"/></svg>
                        </div>
                        <div class="space-y-1.5 max-w-md mx-auto">
                            <h3 class="text-2xl font-semibold text-white tracking-tight">Personal Training</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Get dedicated coach guidance, custom workout programming, and personalized technique feedback.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-lg mx-auto pt-2 text-left">
                            <div class="p-3.5 rounded-[10px] bg-[#0D0E10]/80 border border-[#26282D] space-y-1">
                                <div class="text-lg"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg></div>
                                <div class="text-xs font-semibold text-white">Custom Workouts</div>
                                <p class="text-[10px] text-slate-400 leading-snug">Personalized progressive overload suited to your target.</p>
                            </div>
                            <div class="p-3.5 rounded-[10px] bg-[#0D0E10]/80 border border-[#26282D] space-y-1">
                                <div class="text-lg"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6z"/></svg></div>
                                <div class="text-xs font-semibold text-white">Form Correction</div>
                                <p class="text-[10px] text-slate-400 leading-snug">100% injury prevention with dedicated rep supervision.</p>
                            </div>
                            <div class="p-3.5 rounded-[10px] bg-[#0D0E10]/80 border border-[#26282D] space-y-1">
                                <div class="text-lg"><svg aria-hidden="true" class="w-5 h-5 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18M6 15l5-5 4 3 6-7"/></svg></div>
                                <div class="text-xs font-semibold text-white">3x Faster Results</div>
                                <p class="text-[10px] text-slate-400 leading-snug">Strict accountability with date-wise measurement tracking.</p>
                            </div>
                        </div>

                        <div class="pt-3">
                            <div class="inline-block px-5 py-2.5 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 font-semibold text-xs">
                                Contact the front desk to get started
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- TAB 5: MEMBERSHIP, PASS & VISITS -->
            <div x-show="tab === 'memberships' || tab === 'attendance'" x-cloak class="space-y-4">
                <h1 class="text-2xl font-semibold text-white tracking-tight">Membership</h1>
                <!-- HERO: LUXURY DIGITAL MEMBERSHIP PASS (ATHLETIC STEALTH) -->
                <div class="relative overflow-hidden rounded-[10px] bg-[#141518] border border-[#F5A524]/30 shadow-none p-5 sm:p-6 text-white space-y-5">
                    
                    

                    <!-- Card Top: Gym Branding & Status Badge -->
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if(!empty($resolvedGymLogo))
                                <img src="{{ $resolvedGymLogo }}" alt="{{ $gymName }}" class="h-6 w-auto max-w-[80px] rounded-md object-contain bg-white/10 p-0.5">
                            @else
                                <span class="w-6 h-6 rounded-lg bg-[#F5A524] text-neutral-950 font-semibold text-[10px] flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($gymName, 0, 2)) }}
                                </span>
                            @endif
                            <span class="text-xs font-semibold tracking-wide uppercase text-[#F5A524]">
                                {{ $gymName }}
                            </span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $member->status === 'ACTIVE' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25' : ($member->status === 'SUSPENDED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-700/50 text-slate-300 border border-slate-600/40') }}">
                            {{ $member->status }}
                        </span>
                    </div>

                    <!-- Card Middle: Member Info -->
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="relative shrink-0">
                            @if($member->avatar_url)
                                <img src="{{ $member->avatar_url }}" alt="{{ $member->full_name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-[10px] object-cover border-2 border-[#F5A524]/40 shadow-none">
                            @else
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-[10px] bg-[#1B1D21] flex items-center justify-center text-[#F5A524] font-semibold text-2xl sm:text-3xl shadow-none border border-[#F5A524]/30">
                                    {{ strtoupper(substr($member->first_name ?: 'M', 0, 1)) }}{{ strtoupper(substr($member->last_name ?: '', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-xl sm:text-2xl font-semibold text-white tracking-tight truncate">{{ $member->full_name }}</h2>
                                <span class="px-2 py-0.5 rounded-md bg-white/10 text-slate-200 text-[11px] font-mono font-semibold border border-white/10">
                                    {{ $member->member_code }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-300">
                                @if($activeMembership)
                                    <span class="font-semibold text-[#F5A524]">{{ $activeMembership->plan?->name ?? 'Standard Plan' }}</span>
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
                                <span class="font-semibold text-white ml-1">{{ $activeMembership->end_date?->format('d M, Y') ?? 'N/A' }}</span>
                            </div>
                            @if($daysLeft !== null)
                                <span class="px-3 py-1 rounded-[10px] text-xs font-semibold {{ $daysLeft > 7 ? 'bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30' : ($daysLeft >= 0 ? 'bg-amber-400/15 text-amber-300 border border-amber-400/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30') }}">
                                    {{ $daysLeft >= 0 ? ($daysLeft . ' Days Left') : ('Expired ' . abs($daysLeft) . 'd ago') }}
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Card Action Buttons: 1-Tap Check-in & Pass -->
                    <div class="relative z-10 grid grid-cols-2 gap-2.5 pt-1">
                        <button type="button" 
                                @click="startPortalScanner()" 
                                class="py-3 px-3 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414]  text-neutral-950 font-semibold text-xs shadow-none  flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Check-in / PT</span>
                        </button>

                        <button type="button" 
                                @click="showQrModal = true" 
                                class="py-3 px-3 rounded-[10px] bg-white/10 hover:bg-white/15  border border-white/15 text-white font-semibold text-xs shadow-none flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4 text-[#F5A524] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>View Pass</span>
                        </button>
                    </div>
                </div>

                <!-- Plan Summary Card -->
                <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-5 sm:p-6  space-y-4">
                    <h3 class="text-base font-semibold text-white">Active Plan & Validity</h3>

                    @if($activeMembership)
                        <div class="p-4 rounded-[10px] bg-[#0D0E10]/70 border border-[#26282D] space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs text-slate-400 font-medium">Enrolled Plan</span>
                                    <div class="text-base font-semibold text-white">{{ $activeMembership->plan?->name ?? 'Standard Plan' }}</div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30">
                                    {{ $activeMembership->status }}
                                </span>
                            </div>

                            <div class="flex justify-between text-xs pt-1 border-t border-[#26282D]">
                                <span class="text-slate-400">Duration:</span>
                                <span class="font-semibold text-slate-200">{{ $activeMembership->start_date?->format('d M, Y') }} &rarr; {{ $activeMembership->end_date?->format('d M, Y') }}</span>
                            </div>

                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Total Fee:</span>
                                <span class="font-semibold text-white">{{ $currency }}{{ number_format($activeMembership->final_amount, 2) }}</span>
                            </div>

                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Paid:</span>
                                <span class="font-semibold text-[#F5A524] font-mono">{{ $currency }}{{ number_format($activeMembership->paid_amount, 2) }}</span>
                            </div>

                            @if($remainingAmt > 0)
                                <div class="flex justify-between text-xs pt-1 border-t border-[#26282D]">
                                    <span class="text-rose-400 font-semibold">Balance Due:</span>
                                    <span class="font-semibold text-rose-400 font-mono">{{ $currency }}{{ number_format($remainingAmt, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-slate-400 bg-[#0D0E10]/50 rounded-[10px]">
                            No active membership found.
                        </div>
                    @endif
                </div>

                <!-- GYM ATTENDANCE & VISITS HISTORY LOG -->
                <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-5 sm:p-6  space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-white">Gym Visit &amp; Attendance Log</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Automated check-ins recorded via biometric &amp; QR scan</p>
                        </div>
                        <span class="px-3 py-1 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] text-xs font-semibold border border-[#F5A524]/30 font-mono">
                            {{ $totalCheckins }} Total Visits
                        </span>
                    </div>

                    @if($member->attendance->isNotEmpty())
                        <div class="divide-y divide-neutral-800">
                            @foreach($member->attendance as $att)
                                <div class="py-3 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#F5A524] shrink-0"></div>
                                        <div>
                                            <div class="font-semibold text-white">
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
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-semibold uppercase bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 shrink-0">
                                        {{ $att->status ?? 'Present' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-slate-400 bg-[#0D0E10]/50 rounded-[10px]">
                            No attendance visits recorded yet.
                        </div>
                    @endif
                </div>



                <!-- Invoices & Receipts -->
                <div class="rounded-[10px] bg-[#141518] border border-[#26282D] p-5 sm:p-6  space-y-4">
                    <h3 class="text-base font-semibold text-white">Tax Invoices & Receipts</h3>

                    @if($member->payments->isNotEmpty())
                        <div class="divide-y divide-neutral-800">
                            @foreach($member->payments as $payment)
                                <div class="py-3 flex items-center justify-between text-xs">
                                    <div class="space-y-0.5">
                                        <div class="font-mono font-semibold text-white">{{ $payment->receipt_number }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $payment->payment_date?->format('d M, Y') ?? 'N/A' }} • {{ $payment->payment_method }}</div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-white font-mono">{{ $currency }}{{ number_format($payment->amount, 2) }}</span>
                                        <a href="{{ route('app.invoices.show', $payment->id) }}" target="_blank" class="px-2.5 py-1.5 rounded-[10px] bg-[#F5A524]/15 hover:bg-[#F5A524] text-[#F5A524] hover:text-neutral-950 font-semibold text-xs transition-colors flex items-center gap-1 cursor-pointer">
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

            <!-- MODAL: DIGITAL QR PASS (ATHLETIC STEALTH) -->
            <div x-show="showQrModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0D0E10]/85 ">
                <div @click.away="showQrModal = false" class="w-full max-w-sm rounded-[10px] bg-[#0D0E10] border border-[#26282D] shadow-none p-6 text-center space-y-5 relative text-white">
                    <button type="button" @click="showQrModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                    <div>
                        <span class="px-3 py-1 rounded-full bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 text-[10px] font-semibold uppercase tracking-wider">Gym Check-in Pass</span>
                        <h3 class="text-xl font-semibold text-white mt-2">{{ $member->full_name }}</h3>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $member->member_code }}</p>
                    </div>

                    <!-- QR Code -->
                    <div class="p-4 rounded-[10px] bg-white mx-auto inline-block shadow-none">
                        <img src="https://quickchart.io/qr?size=200&margin=1&text={{ urlencode($member->qr_code_token ?? $member->member_code) }}" alt="QR Code" class="w-44 h-44 mx-auto object-contain" onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($member->qr_code_token ?? $member->member_code) }}';">
                    </div>

                    <p class="text-xs text-slate-400">Scan this pass at the front desk scanner or turnstile to log your attendance.</p>

                    <button type="button" @click="showQrModal = false" class="w-full py-2.5 rounded-[10px] bg-[#1B1D21] hover:bg-slate-700 text-white font-semibold text-xs cursor-pointer">
                        Close
                    </button>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- DUAL-MODE SMART SCANNER MODAL (SCAN QR OR ENTER SESSION PIN) -->
            <!-- ========================================================= -->
            <div x-show="showPortalScanner" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0D0E10]/85 ">
                <div @click.away="if (!pinSubmitting && !pinSuccess) stopPortalScanner()" class="w-full max-w-sm rounded-[10px] bg-[#0D0E10] border border-[#26282D] shadow-none p-5 text-center space-y-4 relative text-white">
                    <template x-if="!pinSubmitting && !pinSuccess">
                        <button type="button" @click="stopPortalScanner()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                    </template>
                    
                    <!-- SUCCESS STATE INSIDE SCANNER -->
                    <template x-if="pinSuccess">
                        <div class="py-6 space-y-3">
                            <div class="w-16 h-16 rounded-full bg-[#F5A524]/20 text-[#F5A524] border border-[#F5A524]/40 flex items-center justify-center text-3xl mx-auto shadow-none animate-bounce">
                                ✓
                            </div>
                            <h3 class="text-lg font-semibold text-white">Session Confirmed!</h3>
                            <p class="text-xs text-slate-300 max-w-xs mx-auto" x-text="pinSuccessMsg"></p>
                            <template x-if="activePendingPt?.remaining_sessions !== undefined">
                                <div class="p-3 rounded-[10px] bg-[#0D0E10] border border-[#26282D] text-xs inline-block">
                                    <span class="text-slate-400">Remaining Balance:</span>
                                    <span class="font-semibold text-[#F5A524] ml-1" x-text="activePendingPt.remaining_sessions + ' Sessions Left'"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- SCANNER / PIN FORM -->
                    <template x-if="!pinSuccess">
                        <div class="space-y-4">
                            <div>
                                <span class="px-3 py-1 rounded-full bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 text-[10px] font-semibold uppercase tracking-wider">Gym &amp; PT Scanner</span>
                                <h3 class="text-base sm:text-lg font-semibold text-white mt-1.5">Scan QR or Enter PIN</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Point camera at Trainer's QR or Front Desk Standee</p>
                            </div>

                            <!-- Dual Toggle Tabs: Camera vs Session PIN -->
                            <div class="flex items-center p-1 rounded-[10px] bg-[#1B1D21] border border-[#26282D] text-xs font-semibold">
                                <button type="button" 
                                        @click="switchScannerTab('camera')" 
                                        :class="scannerMode === 'camera' ? 'bg-[#F5A524] text-neutral-950 font-semibold shadow-none ' : 'text-slate-400 hover:text-white'" 
                                        class="flex-1 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5">
                                    <span>Camera</span>
                                </button>
                                <button type="button" 
                                        @click="switchScannerTab('code')" 
                                        :class="scannerMode === 'code' ? 'bg-[#F5A524] text-neutral-950 font-semibold shadow-none ' : 'text-slate-400 hover:text-white'" 
                                        class="flex-1 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5">
                                    <span>Session PIN</span>
                                </button>
                            </div>

                            <!-- TAB A: CAMERA SCANNER -->
                            <div x-show="scannerMode === 'camera'" class="space-y-3">
                                <div x-show="cameraStarting" class="py-8 text-center text-xs text-[#F5A524]  font-semibold flex items-center justify-center gap-2">
                                    <svg class="animate-spin w-4 h-4 text-[#F5A524]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Starting Camera...</span>
                                </div>

                                <div id="portal-camera-reader" class="w-full aspect-square max-w-[260px] mx-auto rounded-[10px] overflow-hidden bg-black border border-slate-700 "></div>

                                <!-- Camera Error / HTTP Warning Notice -->
                                <div x-show="cameraError || portalScanMsg" class="p-3 rounded-[10px] bg-[#F5A524]/10 border border-[#F5A524]/30 text-slate-200 text-xs text-left space-y-2">
                                    <p class="text-[11px] leading-relaxed" x-text="portalScanMsg || 'Live camera access may be restricted by browser permissions or require HTTPS.'"></p>
                                    <div class="flex items-center gap-2 pt-1">
                                        <button type="button" @click="switchScannerTab('code')" class="flex-1 py-2 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414] text-neutral-950 font-semibold text-[11px] cursor-pointer transition-all text-center">
                                            Enter session PIN
                                        </button>
                                        <label for="portal-file-input" class="py-2 px-3 rounded-[10px] bg-[#1B1D21] hover:bg-slate-700 border border-slate-700 text-white font-semibold text-[11px] cursor-pointer text-center">
                                            Open camera
                                        </label>
                                    </div>
                                </div>

                                <input type="file" id="portal-file-input" accept="image/*" capture="environment" class="hidden" @change="scanQrFile($event)">
                                <p class="text-[11px] text-slate-400">Position the QR code inside the frame to scan automatically.</p>
                            </div>

                            <!-- TAB B: 6-DIGIT SESSION PIN DIRECT CONFIRM -->
                            <div x-show="scannerMode === 'code'" class="space-y-3 py-1">
                                <div class="p-3.5 rounded-[10px] bg-[#0D0E10]/60 border border-[#26282D] space-y-2 text-left">
                                    <label class="block text-xs font-semibold text-slate-300">Enter 6-Digit Session PIN</label>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Type the 6-digit PIN displayed on your trainer's screen (e.g. 210289).</p>
                                    <input type="text" 
                                           x-model="manualOtpCode" 
                                           maxlength="6" 
                                           placeholder="e.g. 210289" 
                                           class="w-full px-4 py-3 rounded-[10px] bg-[#141518] border border-[#F5A524]/40 text-[#F5A524] font-mono font-semibold text-2xl text-center tracking-[0.3em] focus:outline-none focus:border-[#F5A524] focus:ring-1 focus:ring-[#F5A524]">
                                </div>

                                <!-- Quick Star Rating in PIN tab -->
                                <div class="space-y-1 pt-1">
                                    <label class="block text-[11px] font-semibold text-slate-300">Rate Today's Workout</label>
                                    <div class="flex items-center justify-center gap-2 text-2xl">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button type="button" 
                                                    @click="ptRating = star" 
                                                    class="cursor-pointer transition-transform hover:scale-125 focus:outline-none">
                                                <span :class="star <= ptRating ? 'text-[#F5A524] drop-shadow' : 'text-slate-600'">★</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Quick Feedback Chips in PIN tab -->
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button" @click="ptFeedback = 'Pushed to limits, great pump!'" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-[#1B1D21] hover:bg-slate-700 text-slate-300 cursor-pointer">Great session</button>
                                    <button type="button" @click="ptFeedback = 'Focused on correct form & mobility'" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-[#1B1D21] hover:bg-slate-700 text-slate-300 cursor-pointer">Good form</button>
                                    <button type="button" @click="ptFeedback = 'High intensity workout session'" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-[#1B1D21] hover:bg-slate-700 text-slate-300 cursor-pointer">High intensity</button>
                                </div>

                                <!-- Inline Error Banner -->
                                <div x-show="pinErrorMsg" class="p-2.5 rounded-[10px] bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-semibold text-center" x-text="pinErrorMsg"></div>

                                <button type="button" 
                                        @click="submitPinConfirm()" 
                                        :disabled="pinSubmitting"
                                        class="w-full py-3.5 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414]  text-neutral-950 font-semibold text-xs shadow-none  cursor-pointer transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                                    <span x-show="!pinSubmitting">✓ Confirm with Session PIN</span>
                                    <span x-show="pinSubmitting" class="flex items-center gap-1.5">
                                        <svg class="animate-spin w-4 h-4 text-neutral-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Verifying PIN...</span>
                                    </span>
                                </button>
                            </div>

                            <button type="button" @click="stopPortalScanner()" class="w-full py-2.5 rounded-[10px] bg-[#1B1D21] hover:bg-slate-700 text-white font-semibold text-xs cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- PT SESSION CONFIRMATION & RATING MODAL (ATHLETIC STEALTH) -->
            <!-- ========================================================= -->
            <div x-show="showPtConfirmModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0D0E10]/85 ">
                <div @click.away="if (!ptConfirmLoading && !ptConfirmSuccess) showPtConfirmModal = false" class="w-full max-w-sm rounded-[10px] bg-[#0D0E10] border border-[#26282D] shadow-none p-5 sm:p-6 text-center space-y-4 relative text-white">
                    <template x-if="!ptConfirmLoading && !ptConfirmSuccess">
                        <button type="button" @click="showPtConfirmModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                    </template>

                    <!-- Success State -->
                    <template x-if="ptConfirmSuccess">
                        <div class="py-4 space-y-3">
                            <div class="w-16 h-16 rounded-full bg-[#F5A524]/20 text-[#F5A524] border border-[#F5A524]/40 flex items-center justify-center text-3xl mx-auto shadow-none animate-bounce">
                                ✓
                            </div>
                            <h3 class="text-lg font-semibold text-white">Session Confirmed!</h3>
                            <p class="text-xs text-slate-300 max-w-xs mx-auto" x-text="ptSuccessMsg"></p>
                            <template x-if="activePendingPt?.remaining_sessions !== undefined">
                                <div class="p-3 rounded-[10px] bg-[#0D0E10] border border-[#26282D] text-xs inline-block">
                                    <span class="text-slate-400">Remaining Balance:</span>
                                    <span class="font-semibold text-[#F5A524] ml-1" x-text="activePendingPt.remaining_sessions + ' Sessions Left'"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Interactive Confirmation Form -->
                    <template x-if="!ptConfirmSuccess">
                        <div class="space-y-4">
                            <!-- Header with Coach details -->
                            <div class="space-y-1">
                                <span class="px-3 py-1 rounded-full bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 text-[10px] font-semibold uppercase tracking-wider">Workout Completed</span>
                                <h3 class="text-lg font-semibold text-white mt-1">Confirm PT Session</h3>
                                <p class="text-xs text-slate-400">With <span class="font-semibold text-[#F5A524]" x-text="activePendingPt?.trainer_name || 'Your Coach'"></span></p>
                            </div>

                            <!-- Session Summary Badge -->
                            <div class="p-3 rounded-[10px] bg-[#0D0E10]/70 border border-[#26282D] text-xs flex items-center justify-between text-left">
                                <div>
                                    <span class="font-semibold text-white block" x-text="activePendingPt?.package_name || 'Personal Training'"></span>
                                    <span class="text-[11px] text-slate-400 block" x-text="activePendingPt?.focus_area || 'Strength Training'"></span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2 py-0.5 rounded-lg bg-[#F5A524]/15 text-[#F5A524] font-semibold text-[10px] border border-[#F5A524]/30 block" x-text="(activePendingPt?.duration_minutes || 60) + ' Mins'"></span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5" x-text="activePendingPt?.remaining_sessions ? activePendingPt.remaining_sessions + ' Left' : ''"></span>
                                </div>
                            </div>

                            <!-- Star Rating (1 to 5 Stars) -->
                            <div class="space-y-1.5 pt-1">
                                <label class="block text-xs font-semibold text-slate-300">Rate Today's Workout</label>
                                <div class="flex items-center justify-center gap-2 text-2xl">
                                    <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                        <button type="button" 
                                                @click="ptRating = star" 
                                                class="cursor-pointer transition-transform hover:scale-125 focus:outline-none">
                                            <span :class="star <= ptRating ? 'text-[#F5A524] drop-shadow' : 'text-slate-600'">★</span>
                                        </button>
                                    </template>
                                </div>
                                <span class="text-[10px] text-slate-400 block" x-text="ptRating === 5 ? 'Excellent' : (ptRating === 4 ? 'Very good' : (ptRating === 3 ? 'Good' : 'Challenging session'))"></span>
                            </div>

                            <!-- Quick Feedback Chips -->
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <button type="button" @click="ptFeedback = 'Pushed to limits, great pump!'" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-[#1B1D21] hover:bg-slate-700 text-slate-300 cursor-pointer">Great session</button>
                                <button type="button" @click="ptFeedback = 'Focused on correct form & mobility'" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-[#1B1D21] hover:bg-slate-700 text-slate-300 cursor-pointer">Good form</button>
                                <button type="button" @click="ptFeedback = 'High intensity workout session'" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-[#1B1D21] hover:bg-slate-700 text-slate-300 cursor-pointer">High intensity</button>
                            </div>

                            <!-- Optional note textarea -->
                            <div>
                                <textarea x-model="ptFeedback" 
                                          rows="2" 
                                          placeholder="Optional coach feedback or workout note..." 
                                          class="w-full px-3 py-2 rounded-[10px] bg-[#0D0E10] border border-[#26282D] text-xs text-white focus:outline-none focus:border-[#F5A524] resize-none"></textarea>
                            </div>

                            <!-- Confirm & Deduct Button -->
                            <div class="pt-1">
                                <button type="button" 
                                        @click="submitPtConfirm()" 
                                        :disabled="ptConfirmLoading" 
                                        class="w-full py-3.5 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414]  text-neutral-950 font-semibold text-xs shadow-none  flex items-center justify-center gap-2 cursor-pointer transition-all disabled:opacity-50">
                                    <span x-show="!ptConfirmLoading">✓ Confirm &amp; Deduct 1 Session</span>
                                    <span x-show="ptConfirmLoading" class="flex items-center gap-1.5">
                                        <svg class="animate-spin w-4 h-4 text-neutral-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Confirming...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- IN-APP EXERCISE VIDEO PLAYER MODAL (CHAMPAGNE STEALTH)    -->
            <!-- ========================================================= -->
            <template x-if="activeVideo">
                <div @keydown.escape.window="closeExerciseVideo()"
                     @click.self="closeExerciseVideo()"
                     class="fixed inset-0 z-[90] flex items-center justify-center p-3 sm:p-5 bg-black/85 ">
                    <div @click.stop class="w-full max-w-2xl rounded-[10px] bg-[#12151C] border border-[#26282D] shadow-none overflow-hidden text-white">
                        <!-- Modal Header -->
                        <div class="flex items-start justify-between gap-3 p-4 sm:p-5 border-b border-[#26282D] bg-[#141518]">
                            <div class="min-w-0 space-y-1.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 text-[10px] font-semibold uppercase tracking-wider"
                                          x-text="activeVideo.source || 'Video Library'"></span>
                                    <span x-show="activeVideo.category" class="px-2 py-0.5 rounded-md bg-[#0D0E10] border border-[#26282D] text-[10px] font-semibold text-slate-300"
                                          x-text="activeVideo.category"></span>
                                    <span x-show="activeVideo.equipment" class="px-2 py-0.5 rounded-md bg-[#0D0E10] border border-[#26282D] text-[10px] font-semibold text-slate-400"
                                          x-text="activeVideo.equipment"></span>
                                </div>
                                <h3 class="text-base sm:text-lg font-semibold text-white leading-tight break-words" x-text="activeVideo.title"></h3>
                            </div>
                            <button type="button"
                                    @click="closeExerciseVideo()"
                                    aria-label="Close video player"
                                    class="w-9 h-9 rounded-[10px] bg-[#0D0E10] hover:bg-neutral-800 border border-[#26282D] text-slate-300 hover:text-white flex items-center justify-center font-semibold shrink-0 cursor-pointer transition-colors">
                                ✕
                            </button>
                        </div>

                        <!-- Video Viewport -->
                        <div class="bg-black aspect-video w-full flex items-center justify-center relative">
                            <template x-if="resolveVideoKind(activeVideo.url) === 'mp4'">
                                <video id="portal-exercise-video-player"
                                       :key="activeVideo.url"
                                       controls
                                       autoplay
                                       loop
                                       playsinline
                                       preload="metadata"
                                       class="w-full h-full object-contain bg-black">
                                    <source :src="activeVideo.url">
                                    Your browser cannot play this video directly.
                                </video>
                            </template>

                            <template x-if="resolveVideoKind(activeVideo.url) === 'embed'">
                                <iframe :key="activeVideo.url"
                                        :src="resolveEmbedUrl(activeVideo.url)"
                                        class="w-full h-full border-0"
                                        allow="autoplay; encrypted-media; picture-in-picture"
                                        allowfullscreen></iframe>
                            </template>

                            <template x-if="resolveVideoKind(activeVideo.url) === 'link'">
                                <div class="p-8 text-center space-y-3">
                                    <div class="w-12 h-12 rounded-[10px] bg-[#F5A524]/15 text-[#F5A524] border border-[#F5A524]/30 flex items-center justify-center mx-auto text-xl">▶</div>
                                    <p class="text-xs text-slate-300">This demonstration opens externally.</p>
                                    <a :href="activeVideo.url" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[10px] bg-[#F5A524] text-neutral-950 text-xs font-semibold">
                                        Open Demonstration Video &nearr;
                                    </a>
                                </div>
                            </template>
                        </div>

                        <!-- Details & Actions Footer -->
                        <div class="p-4 sm:p-5 space-y-4 bg-[#141518] border-t border-[#26282D]">
                            <!-- Distinct Prescription Cards: Sets & Reps -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <!-- SETS -->
                                <div class="rounded-[10px] bg-[#0D0E10] border border-[#26282D]/90 p-3 text-center ">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block"
                                          x-text="activeVideo.trackingType === 'time' ? 'Rounds' : 'Sets'">Sets</span>
                                    <span class="mt-1 text-lg sm:text-xl font-semibold font-mono text-[#F5A524] block tabular-nums"
                                          x-text="activeVideo.sets || '3'"></span>
                                </div>

                                <!-- REPS / DURATION -->
                                <div class="rounded-[10px] bg-[#0D0E10] border border-[#26282D]/90 p-3 text-center ">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block"
                                          x-text="activeVideo.trackingType === 'time' ? 'Duration' : 'Reps'">Reps</span>
                                    <span class="mt-1 text-lg sm:text-xl font-semibold font-mono text-white block tabular-nums"
                                          x-text="activeVideo.reps || activeVideo.setsReps || '10-12'"></span>
                                </div>

                                <!-- REST (if available) -->
                                <div x-show="activeVideo.rest" class="rounded-[10px] bg-[#0D0E10] border border-[#26282D]/90 p-3 text-center ">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Rest</span>
                                    <span class="mt-1 text-lg sm:text-xl font-semibold font-mono text-slate-200 block tabular-nums"
                                          x-text="activeVideo.rest"></span>
                                </div>

                                <!-- WEIGHT / LOAD (if available) -->
                                <div x-show="activeVideo.weight" class="rounded-[10px] bg-[#0D0E10] border border-[#26282D]/90 p-3 text-center ">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Load</span>
                                    <span class="mt-1 text-sm sm:text-base font-semibold font-mono text-[#F5A524] block truncate"
                                          x-text="activeVideo.weight"></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-1">
                                <button type="button"
                                        @click="closeExerciseVideo()"
                                        class="w-full sm:w-auto px-6 py-2.5 rounded-[10px] bg-[#F5A524] hover:bg-[#E09414] text-neutral-950 text-xs font-semibold cursor-pointer transition-colors ">
                                    Close Video
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        @endif

    </div>
</x-app-layout>