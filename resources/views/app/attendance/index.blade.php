<x-app-layout header="Gym Attendance & Live Access">

    @push('styles')
    <style>
        .attendance-workspace { min-width:0; font-variant-numeric:tabular-nums; }
        .attendance-workspace .attendance-header { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .attendance-workspace .attendance-heading { display:flex; align-items:center; flex-wrap:wrap; gap:10px; }
        .attendance-workspace .attendance-header-actions { display:flex; flex-wrap:wrap; gap:8px; }
        .attendance-workspace .attendance-kpis { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .attendance-workspace .attendance-kpi { padding:14px 16px; border:1px solid rgb(var(--c-line)); border-top:2px solid rgb(var(--kpi-tone,var(--c-line-strong))); border-radius:12px; background:rgb(var(--c-surface)); min-width:0; }
        .attendance-workspace .attendance-kpi-label { display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:12px; color:rgb(var(--c-fg-muted)); font-weight:500; }
        .attendance-workspace .attendance-kpi-label svg { color:rgb(var(--kpi-tone,var(--c-fg-muted))); flex-shrink:0; }
        .attendance-workspace .attendance-kpi-value { font-size:24px; font-weight:600; color:rgb(var(--kpi-tone,var(--c-fg))); margin-top:7px; line-height:1.3; }
        .attendance-workspace .attendance-kpi-caption { color:rgb(var(--c-fg-muted)); font-size:12px; margin-top:4px; }
        .attendance-workspace .attendance-panel { background:rgb(var(--c-surface)); border:1px solid rgb(var(--c-line)); border-radius:12px; min-width:0; }
        .attendance-workspace .attendance-panel-heading { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; padding:14px 16px; border-bottom:1px solid rgb(var(--c-line)); }
        .attendance-workspace .attendance-terminal { padding:16px; display:grid; gap:12px; }
        .attendance-workspace .attendance-search-row { display:flex; align-items:center; gap:8px; }
        .attendance-workspace .attendance-search { flex:1; min-width:0; }
        .attendance-workspace .attendance-search input { width:100%; height:44px; padding:0 106px 0 40px; background:rgb(var(--c-canvas)); border:1px solid rgb(var(--c-line-strong)); border-radius:8px; font-size:14px; color:rgb(var(--c-fg)); }
        .attendance-workspace .attendance-search input:focus { border-color:rgb(var(--c-accent)); }
        .attendance-workspace .attendance-checkin { height:44px; padding-inline:16px; }
        .attendance-workspace .attendance-method-hints { display:flex; align-items:center; flex-wrap:wrap; gap:16px; color:rgb(var(--c-fg-muted)); font-size:12px; }
        .attendance-workspace .attendance-method-hints > span { display:inline-flex; align-items:center; gap:6px; }
        .attendance-workspace .attendance-policy { display:inline-flex; align-items:center; gap:6px; padding:5px 8px; border-radius:6px; font-size:12px; font-weight:500; color:rgb(var(--c-warning)); background:rgb(var(--c-warning)/.10); border:1px solid rgb(var(--c-warning)/.24); }
        .attendance-workspace .attendance-feedback { padding:12px; border-radius:8px; border:1px solid rgb(var(--feedback-tone)/.30); background:rgb(var(--feedback-tone)/.08); color:rgb(var(--feedback-tone)); }
        .attendance-workspace .attendance-feedback-success { --feedback-tone:var(--c-success); }
        .attendance-workspace .attendance-feedback-error { --feedback-tone:var(--c-danger); }
        .attendance-workspace .attendance-feedback h4 { color:rgb(var(--feedback-tone)); }
        .attendance-workspace .attendance-table-scroll { overflow:auto; max-height:min(70vh,760px); }
        .attendance-workspace .attendance-table { min-width:760px; font-size:13px; }
        .attendance-workspace .attendance-table thead { background:rgb(var(--c-raised)); position:sticky; top:0; z-index:1; }
        .attendance-workspace .attendance-table th { padding:10px 12px; font-size:12px; font-weight:500; white-space:nowrap; }
        .attendance-workspace .attendance-table td { padding:12px; vertical-align:middle; }
        .attendance-workspace .attendance-member-avatar { border:1px solid rgb(var(--c-line)); color:rgb(var(--c-fg-muted)); background:rgb(var(--c-raised)); }
        .attendance-workspace .attendance-checkout { display:inline-flex; align-items:center; justify-content:center; gap:6px; min-height:34px; border-radius:8px; padding:6px 10px; font-size:12px; font-weight:500; white-space:nowrap; color:rgb(var(--c-warning)); background:rgb(var(--c-warning)/.10); border:1px solid rgb(var(--c-warning)/.24); cursor:pointer; }
        .attendance-workspace .attendance-checkout:hover { background:rgb(var(--c-warning)/.18); }
        .attendance-workspace .attendance-mobile-details { padding:10px 0; border-block:1px solid rgb(var(--c-line)); }
        .attendance-workspace .attendance-access { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:rgb(var(--c-fg-muted)); }
        .attendance-workspace .attendance-scanner { width:min(100%,440px); max-height:calc(100dvh - 24px); overflow:auto; padding:20px; border-radius:12px; background:rgb(var(--c-surface)); border:1px solid rgb(var(--c-line)); color:rgb(var(--c-fg)); }
        @media(max-width:640px) {
            .attendance-workspace .attendance-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
            .attendance-workspace .attendance-kpi:first-child { grid-column:1/-1; }
            .attendance-workspace .attendance-kpi { padding:12px; }
            .attendance-workspace .attendance-header-actions { width:100%; }
            .attendance-workspace .attendance-header-actions>* { flex:1; }
            .attendance-workspace .attendance-panel-heading,.attendance-workspace .attendance-terminal { padding:12px; }
            .attendance-workspace .attendance-search-row { flex-direction:column; align-items:stretch; }
            .attendance-workspace .attendance-search input { padding-right:36px; }
            .attendance-workspace .attendance-checkin { width:100%; }
            .attendance-workspace .attendance-method-hints { gap:10px; }
        }
    </style>
    @endpush

    <div x-data="{
        showScannerModal: false,
        searchQuery: '',
        searching: false,
        scanning: false,
        feedback: null,
        html5QrCode: null,
        membersList: {{ Js::from($membersJson ?? []) }},
        showDropdown: false,
        selectedMember: null,

        get filteredMembers() {
            let q = this.searchQuery.trim().toLowerCase();
            if (!q) return [];
            return this.membersList.filter(m => {
                return (m.name && m.name.toLowerCase().includes(q)) ||
                       (m.code && m.code.toLowerCase().includes(q)) ||
                       (m.phone && m.phone.includes(q));
            }).slice(0, 8);
        },

        submitCheckin(memberId = null, code = null) {
            let payload = {};
            if (memberId) {
                payload.member_id = memberId;
            } else if (code) {
                payload.code = code.trim();
            } else {
                let q = this.searchQuery.trim();
                if (!q) return;
                // If there is an exact or single filtered member, use their id
                let matches = this.filteredMembers;
                if (matches.length === 1) {
                    payload.member_id = matches[0].id;
                } else {
                    payload.code = q;
                }
            }

            this.scanning = true;
            this.feedback = null;
            this.showDropdown = false;

            fetch('{{ route('app.attendance.qr-scan') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                this.scanning = false;
                if (status >= 200 && status < 300 && body.success) {
                    this.feedback = {
                        type: 'success',
                        title: 'Check-In Successful',
                        message: body.message,
                        member: body.member
                    };
                    this.searchQuery = '';
                    // Reload softly after 1.5s to refresh attendance stream and counters
                    setTimeout(() => { window.location.reload(); }, 1400);
                } else {
                    this.feedback = {
                        type: 'error',
                        title: status === 403 ? 'Membership Inactive / Expired' : 'Check-In Failed',
                        message: body.message || 'Unable to record check-in.',
                        member: body.member || null
                    };
                }
            })
            .catch(err => {
                this.scanning = false;
                this.feedback = {
                    type: 'error',
                    title: 'System Error',
                    message: 'Network error or server unavailable: ' + err.message
                };
            });
        },

        startCameraScanner() {
            this.showScannerModal = true;
            this.$nextTick(() => {
                if (typeof Html5Qrcode !== 'undefined') {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().catch(() => {});
                    }
                    this.html5QrCode = new Html5Qrcode('camera-reader');
                    this.html5QrCode.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            this.stopCameraScanner();
                            this.submitCheckin(null, decodedText);
                        },
                        (error) => {}
                    ).catch(err => {
                        this.feedback = {
                            type: 'error',
                            title: 'Camera Access Error',
                            message: 'Could not access device camera: ' + err
                        };
                    });
                }
            });
        },

        stopCameraScanner() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                }).catch(() => {});
            }
            this.showScannerModal = false;
        }
    }" class="space-y-4 attendance-workspace">

        {{-- ══════════════════════════════════════════════════════════════
             1. HEADER & ACTION STRIP
        ══════════════════════════════════════════════════════════════ --}}
        <div class="attendance-header">
            <div>
                <div class="attendance-heading">
                    <h1 class="ds-title">Attendance</h1>
                </div>
                <p class="ds-caption mt-1">Manage member access, check-ins and live gym occupancy.</p>
            </div>
            <div class="attendance-header-actions">
                <a href="{{ route('app.attendance.standee') }}" target="_blank"
                   class="ds-btn ds-btn-secondary">
                    <svg class="w-4 h-4 text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Desk QR standee</span>
                </a>
                <button type="button" @click="startCameraScanner()"
                        class="ds-btn ds-btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Camera scan</span>
                </button>
            </div>
        </div>
        <section class="attendance-kpis" aria-label="Attendance summary">
            <div class="attendance-kpi" style="--kpi-tone:var(--c-success)">
                <div class="attendance-kpi-label">Currently inside <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg></div>
                <div class="attendance-kpi-value">{{ $summary['currently_inside'] }}</div>
                <div class="attendance-kpi-caption">Members on the gym floor</div>
            </div>
            <div class="attendance-kpi">
                <div class="attendance-kpi-label">Check-ins today <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3m10-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg></div>
                <div class="attendance-kpi-value">{{ $summary['total_present'] }}</div>
                <div class="attendance-kpi-caption">{{ now()->format('l, d M Y') }}</div>
            </div>
            <div class="attendance-kpi">
                <div class="attendance-kpi-label">Completed workouts <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4m7 2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/></svg></div>
                <div class="attendance-kpi-value">{{ $summary['checked_out'] }}</div>
                <div class="attendance-kpi-caption">Sessions ended</div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════
             3. UNIFIED FRONT-DESK CHECK-IN TERMINAL (SINGLE HERO BOX)
        ══════════════════════════════════════════════════════════════ --}}
        <div class="attendance-panel">
            <div class="attendance-panel-heading">
                <div>
                    <h2 class="ds-heading flex items-center gap-2"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8V4h4m8 0h4v4m0 8v4h-4m-8 0H4v-4M4 12h16"/></svg> Member check-in</h2>
                    <p class="ds-caption mt-1">Search for a member or scan their membership pass.</p>
                </div>
            </div>
            <div class="attendance-terminal">
            {{-- Main Search Bar with Autocomplete Dropdown --}}
            <div class="relative" @click.away="showDropdown = false">
                <div class="attendance-search-row">
                    <div class="relative attendance-search">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-fg-muted">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               @focus="showDropdown = true"
                               @input="showDropdown = true"
                               @keydown.enter.prevent="submitCheckin()"
                               placeholder="Member name, phone, ID or barcode" aria-label="Search member for check-in"
                               class="placeholder-fg-subtle transition-colors">
                        
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-2">
                            <button type="button" 
                                    x-show="searchQuery" 
                                    @click="searchQuery = ''; showDropdown = false" 
                                    class="text-fg-muted hover:text-fg p-1 text-xs">
                                ✕
                            </button>
                            <span class="hidden sm:inline-block px-2 py-0.5 rounded bg-raised border border-line text-xs text-fg-muted font-mono">
                                Enter
                            </span>
                        </div>
                    </div>

                    <button type="button"
                            @click="submitCheckin()"
                            :disabled="scanning"
                            class="ds-btn ds-btn-primary attendance-checkin shrink-0">
                        <span x-show="scanning">Checking in…</span>
                        <span x-show="!scanning" class="inline-flex items-center gap-2"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3m10-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg> Check in</span>
                    </button>
                </div>

                {{-- Autocomplete Dropdown Popover --}}
                <div x-show="showDropdown && filteredMembers.length > 0"
                     x-transition
                     class="absolute z-30 left-0 right-0 mt-2 rounded-xl bg-surface border border-line overflow-hidden divide-y divide-line max-h-[380px] overflow-y-auto">
                    <div class="px-4 py-2 bg-raised text-xs font-semibold text-fg-muted flex items-center justify-between">
                        <span>Matching Members (<span x-text="filteredMembers.length"></span>)</span>
                        <span>Click or select to Check In</span>
                    </div>

                    <template x-for="m in filteredMembers" :key="m.id">
                        <div @click="submitCheckin(m.id)"
                             class="p-3.5 hover:bg-raised transition-colors flex items-center justify-between gap-3 cursor-pointer group">
                            <div class="flex items-center gap-3 min-w-0">
                                <template x-if="m.avatar">
                                    <img :src="m.avatar" :alt="m.name" class="w-10 h-10 rounded-full object-cover border border-line shrink-0">
                                </template>
                                <template x-if="!m.avatar">
                                    <div class="w-10 h-10 rounded-full bg-raised attendance-member-avatar font-semibold text-xs flex items-center justify-center shrink-0" x-text="m.initials"></div>
                                </template>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-fg text-sm group-hover:text-emerald-400 transition-colors truncate" x-text="m.name"></span>
                                        <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold bg-raised text-fg-muted border border-line" x-text="m.code"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-fg-muted mt-0.5">
                                        <span x-text="m.phone"></span>
                                        <span>&bull;</span>
                                        <span class="text-fg-muted font-medium" x-text="m.plan"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2">
                                <template x-if="m.is_inside">
                                    <span class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold flex items-center gap-1.5">
                                        <span class="ds-dot"></span>
                                        Inside Gym
                                    </span>
                                </template>
                                <template x-if="!m.is_inside">
                                    <button type="button"
                                            class="ds-btn ds-btn-primary">
                                        <span>Check in</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="attendance-method-hints"><span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18v12H3zM6 10h.01M10 10h.01M14 10h.01M18 10h.01M6 14h12"/></svg> Manual search</span><span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5v14m4-14v14m4-14v14m3-14v14m5-14v14"/></svg> Barcode scanner</span><span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zm7 0v7h-7"/></svg> QR access</span></div>
            {{-- Live Feedback Alert Banner --}}
            <template x-if="feedback">
                <div class="attendance-feedback" role="status" aria-live="polite"
                     :class="feedback.type === 'success' ? 'attendance-feedback-success' : 'attendance-feedback-error'">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span x-show="feedback.type === 'success'"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4m7 2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/></svg></span><span x-show="feedback.type !== 'success'"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 21h20L12 3Zm0 6v5m0 3h.01"/></svg></span>
                            <div>
                                <h4 class="font-semibold text-sm" x-text="feedback.title"></h4>
                                <p class="text-xs mt-0.5 text-fg-muted" x-text="feedback.message"></p>
                                
                                <template x-if="feedback.member">
                                    <div class="mt-2 pt-2 border-t border-line flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-fg-muted">
                                        <span>Member: <strong class="text-fg" x-text="feedback.member.name"></strong> (<span x-text="feedback.member.code"></span>)</span>
                                        <template x-if="feedback.member.auto_checkout">
                                            <span class="text-warning">Auto-checkout: <strong x-text="feedback.member.auto_checkout"></strong></span>
                                        </template>
                                        <template x-if="feedback.member.plan">
                                            <span>Plan: <strong x-text="feedback.member.plan"></strong></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="feedback = null" class="text-fg-muted hover:text-fg text-xs">✕</button>
                    </div>
                </div>
            </template>

            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             4. LIVE ATTENDANCE STREAM & HISTORY TABLE
        ══════════════════════════════════════════════════════════════ --}}
        <div class="attendance-panel">
            <div class="attendance-panel-heading">
                <div>
                    <div class="flex items-center gap-2"><h2 class="ds-heading">Today's attendance</h2><span class="ds-badge ds-badge-neutral">{{ $attendance->total() }}</span></div>
                    <p class="ds-caption mt-1">Track member arrivals and departures.</p>
                </div>
                <span class="attendance-access"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v15H4zM8 3v6m8-6v6M4 11h16"/></svg> {{ now()->format('D, d M Y') }}</span>
            </div>
            <!-- Mobile View: Clean Card Stream -->
            <div class="block md:hidden divide-y divide-line">
                @forelse($attendance as $att)
                    @php
                        $checkInTime = $att->check_in ? \Carbon\Carbon::parse($att->check_in) : null;
                        $autoExitTime = $checkInTime ? $checkInTime->copy()->addMinutes(60) : null;
                        $isInsideNow = empty($att->check_out);
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($att->member?->avatar_url)
                                    <img src="{{ $att->member->avatar_url }}" alt="{{ $att->member->full_name }}" class="w-10 h-10 rounded-full object-cover border border-line shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-raised flex items-center justify-center attendance-member-avatar font-semibold text-xs shrink-0">
                                        {{ strtoupper(substr($att->member->first_name ?? 'M', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="font-semibold text-fg text-sm block truncate">{{ $att->member->full_name ?? 'Member' }}</span>
                                    <span class="text-xs text-fg-muted font-mono">{{ $att->member->member_code ?? '' }}</span>
                                </div>
                            </div>

                            @if($isInsideNow)
                                <span class="ds-badge ds-badge-success shrink-0">
                                    <span class="ds-dot"></span>
                                    Inside
                                </span>
                            @else
                                <span class="ds-badge ds-badge-neutral shrink-0">
                                    Completed
                                </span>
                            @endif
                        </div>

                        <div class="attendance-mobile-details grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-fg-muted block text-xs font-semibold">Check-In</span>
                                <span class="font-semibold text-success">{{ $checkInTime ? $checkInTime->format('h:i:s A') : '—' }}</span>
                            </div>
                            <div>
                                <span class="text-fg-muted block text-xs font-semibold">Check-Out</span>
                                @if($att->check_out)
                                    <span class="font-medium text-fg-muted">{{ \Carbon\Carbon::parse($att->check_out)->format('h:i:s A') }}</span>
                                @else
                                    <span class="font-medium text-amber-400/90">Auto: {{ $autoExitTime ? $autoExitTime->format('h:i A') : '1 hr' }}</span>
                                @endif
                            </div>
                        </div>

                        @if($isInsideNow)
                            <form action="{{ route('app.attendance.checkout', $att->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="attendance-checkout w-full">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 7l5 5-5 5m5-5H7M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5"/></svg><span>Check out</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-fg-muted text-xs">No attendance records found for today.</div>
                @endforelse
            </div>

            <!-- Desktop View: Clean Executive Table -->
            <div class="hidden md:block attendance-table-scroll">
                <table class="attendance-table w-full text-left">
                    <thead class="bg-raised border-b border-line text-fg-muted">
                        <tr>
                            <th class="py-4 px-5">Member</th>
                            <th class="py-4 px-5">Check-in</th>
                            <th class="py-4 px-5">Check-out</th>
                            <th class="py-4 px-5">Access method</th>
                            <th class="py-4 px-5">Status</th>
                            <th class="py-4 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-fg-muted">
                        @forelse($attendance as $att)
                            @php
                                $checkInTime = $att->check_in ? \Carbon\Carbon::parse($att->check_in) : null;
                                $autoExitTime = $checkInTime ? $checkInTime->copy()->addMinutes(60) : null;
                                $isInsideNow = empty($att->check_out);
                            @endphp
                            <tr class="hover:bg-raised transition-colors">
                                
                                {{-- Member Info --}}
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        @if($att->member?->avatar_url)
                                            <img src="{{ $att->member->avatar_url }}" alt="{{ $att->member->full_name }}" class="w-9 h-9 rounded-full object-cover border border-line shrink-0">
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-raised flex items-center justify-center attendance-member-avatar font-semibold text-xs shrink-0">
                                                {{ strtoupper(substr($att->member->first_name ?? 'M', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-semibold text-fg text-xs block">{{ $att->member->full_name ?? 'Member' }}</span>
                                            <div class="flex items-center gap-1.5 text-xs text-fg-muted">
                                                <span class="font-mono">{{ $att->member->member_code ?? '' }}</span>
                                                <span>&bull;</span>
                                                <span class="text-fg-muted">{{ $att->member->activeMembership?->plan?->name ?? 'Active' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Check-In Time --}}
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-semibold text-success text-xs">
                                            {{ $checkInTime ? $checkInTime->format('h:i:s A') : '—' }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-fg-muted block">{{ $att->date ? $att->date->format('M d, Y') : '' }}</span>
                                </td>

                                {{-- Check-Out Time / 1 Hr Auto-Exit --}}
                                <td class="py-3.5 px-5">
                                    @if($att->check_out)
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-fg-muted font-medium">
                                                {{ \Carbon\Carbon::parse($att->check_out)->format('h:i:s A') }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-xs bg-raised text-fg-muted font-mono">
                                                {{ $att->status === 'AUTO_CHECKOUT' ? 'Auto (1 hr)' : 'Manual' }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="attendance-policy">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v4l3 2m7-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/></svg>
                                                Auto due: {{ $autoExitTime ? $autoExitTime->format('h:i A') : '1 hr' }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Method --}}
                                <td class="py-3.5 px-5">
                                    <span class="attendance-access">
                                        @if($att->method === 'qr')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zm7 0v7h-7"/></svg><span>QR Pass</span>
                                        @elseif($att->method === 'facial')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8V4h4m8 0h4v4m0 8v4h-4m-8 0H4v-4M9 10h.01M15 10h.01M9 15q3 3 6 0"/></svg><span>Face Terminal</span>
                                        @elseif($att->method === 'biometric')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12a8 8 0 1 1 16 0M7 12a5 5 0 1 1 10 0c0 5-1 7-2 9M10 12a2 2 0 1 1 4 0c0 4-1 7-2 9M7 16c0 2-1 4-2 5"/></svg><span>Biometric</span>
                                        @else
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18v12H3zM6 10h.01M10 10h.01M14 10h.01M18 10h.01M6 14h12"/></svg><span>Front Desk</span>
                                        @endif
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="py-3.5 px-5">
                                    @if($isInsideNow)
                                        <span class="ds-badge ds-badge-success">
                                            <span class="ds-dot"></span>
                                            Inside
                                        </span>
                                    @else
                                        <span class="ds-badge ds-badge-neutral">
                                            <span>✓</span>
                                            Completed
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="py-3.5 px-5 text-right">
                                    @if($isInsideNow)
                                        <form action="{{ route('app.attendance.checkout', $att->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    title="Member leaving before 1 hr" 
                                                    class="attendance-checkout">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 7l5 5-5 5m5-5H7M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5"/></svg><span>Check out</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-fg-muted font-mono">
                                            Logged
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-fg-muted">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <div class="flex justify-center text-fg-muted"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v15H4zM8 3v6m8-6v6M4 11h16"/></svg></div>
                                        <div class="font-semibold text-fg text-sm">No attendance logged today</div>
                                        <p class="text-xs text-fg-muted">Scan member passes or search active members above to start recording.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendance->hasPages())
                <div class="p-3 sm:p-4 border-t border-line">
                    {{ $attendance->links() }}
                </div>
            @endif

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             5. CAMERA QR SCANNER MODAL
        ══════════════════════════════════════════════════════════════ --}}
        <div x-show="showScannerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-black/70">
            <div @click.away="stopCameraScanner()" class="attendance-scanner relative text-center space-y-4">
                <button type="button" @click="stopCameraScanner()" class="absolute top-4 right-4 text-fg-muted hover:text-fg text-lg cursor-pointer">&times;</button>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto text-xl border border-emerald-500/20">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h4l2-3h6l2 3h4v13H3zM16 13a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-fg">Camera QR scanner</h3>
                    <p class="text-xs text-fg-muted mt-1">Point your camera at the member's digital QR pass</p>
                </div>
                
                <div id="camera-reader" class="w-full aspect-square max-w-[300px] mx-auto rounded-xl overflow-hidden bg-black border border-line"></div>

                <button type="button" @click="stopCameraScanner()" class="ds-btn ds-btn-secondary w-full">
                    Close scanner
                </button>
            </div>
        </div>

    </div>

    <!-- html5-qrcode scanner script -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</x-app-layout>
