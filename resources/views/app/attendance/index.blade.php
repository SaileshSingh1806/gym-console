<x-app-layout header="Gym Attendance & Live Access">
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
    }" class="space-y-6">

        {{-- ══════════════════════════════════════════════════════════════
             1. HEADER & ACTION STRIP
        ══════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Live Attendance &amp; Check-In Hub
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Live Gate Active
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                    <span>Front desk quick search, barcode scanner gun, and entrance QR scanner</span>
                    <span>&bull;</span>
                    <span class="text-amber-600 dark:text-amber-400 font-medium">⏱️ Auto-checkout after 1 hour</span>
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('app.attendance.standee') }}" target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-slate-400 dark:hover:border-slate-700 font-semibold text-xs transition-all flex items-center gap-2 shadow-xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Desk QR Standee</span>
                </a>

                <button type="button" @click="startCameraScanner()"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Camera Scan</span>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             2. LIVE METRICS BENTO CARDS
        ══════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            {{-- Currently Inside Gym --}}
            <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-emerald-950/30 via-slate-900 to-slate-900 border border-emerald-500/30 dark:border-emerald-500/30 shadow-lg shadow-emerald-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Currently on Floor
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        Live Now
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-4xl font-black text-white tracking-tight">{{ $summary['currently_inside'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">members working out</span>
                </div>
                <p class="mt-2 text-[11px] text-slate-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Auto-checkout logged after 60 mins</span>
                </p>
            </div>

            {{-- Total Present Today --}}
            <div class="p-5 rounded-2xl bg-slate-900/90 dark:bg-slate-900 border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Total Check-Ins Today
                    </span>
                    <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-4xl font-black text-white tracking-tight">{{ $summary['total_present'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">unique visits</span>
                </div>
                <p class="mt-2 text-[11px] text-slate-400">
                    {{ now()->format('l, d M Y') }}
                </p>
            </div>

            {{-- Completed & Auto Checked Out --}}
            <div class="p-5 rounded-2xl bg-slate-900/90 dark:bg-slate-900 border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Completed Workouts
                    </span>
                    <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-4xl font-black text-slate-200 tracking-tight">{{ $summary['checked_out'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">sessions ended</span>
                </div>
                <p class="mt-2 text-[11px] text-slate-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Finalized &amp; left facility</span>
                </p>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             3. UNIFIED FRONT-DESK CHECK-IN TERMINAL (SINGLE HERO BOX)
        ══════════════════════════════════════════════════════════════ --}}
        <div class="relative rounded-3xl bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border border-slate-800 p-5 sm:p-7 shadow-2xl space-y-4">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/80 pb-4">
                <div>
                    <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                        <span>⚡</span>
                        <span>Front Desk Rapid Check-In</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Type member name, phone number, member code, or scan pass with barcode gun
                    </p>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="px-2 py-1 rounded-md bg-slate-800/80 border border-slate-700/80 font-mono text-[10px] text-amber-300">Tip:</span>
                    <span>No manual checkout required &bull; Auto-checkout in 1 hr</span>
                </div>
            </div>

            {{-- Main Search Bar with Autocomplete Dropdown --}}
            <div class="relative" @click.away="showDropdown = false">
                <div class="flex items-center gap-2.5">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               @focus="showDropdown = true"
                               @input="showDropdown = true"
                               @keydown.enter.prevent="submitCheckin()"
                               placeholder="Search active member by Name, Phone (e.g. 9876...), Member Code (ARM177) or Scan Pass..."
                               class="w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-950 border border-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white placeholder-slate-500 text-sm font-medium tracking-wide transition-all outline-none">
                        
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center gap-2">
                            <button type="button" 
                                    x-show="searchQuery" 
                                    @click="searchQuery = ''; showDropdown = false" 
                                    class="text-slate-400 hover:text-white p-1 text-xs">
                                ✕
                            </button>
                            <span class="hidden sm:inline-block px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] text-slate-400 font-mono">
                                Press ↵ Enter
                            </span>
                        </div>
                    </div>

                    <button type="button"
                            @click="submitCheckin()"
                            :disabled="scanning"
                            class="px-6 py-4 rounded-2xl bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-slate-950 font-black text-sm transition-all flex items-center gap-2 shadow-lg shadow-emerald-500/20 cursor-pointer shrink-0 disabled:opacity-50">
                        <span x-show="scanning" class="animate-spin text-sm">⏳</span>
                        <span x-show="!scanning">⚡ Check In</span>
                    </button>
                </div>

                {{-- Autocomplete Dropdown Popover --}}
                <div x-show="showDropdown && filteredMembers.length > 0"
                     x-transition
                     class="absolute z-30 left-0 right-0 mt-2 rounded-2xl bg-slate-900 border border-slate-700/80 shadow-2xl overflow-hidden divide-y divide-slate-800/80 max-h-[380px] overflow-y-auto">
                    <div class="px-4 py-2 bg-slate-950/60 text-[10px] uppercase font-bold tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Matching Members (<span x-text="filteredMembers.length"></span>)</span>
                        <span>Click or select to Check In</span>
                    </div>

                    <template x-for="m in filteredMembers" :key="m.id">
                        <div @click="submitCheckin(m.id)"
                             class="p-3.5 hover:bg-slate-800/70 transition-colors flex items-center justify-between gap-3 cursor-pointer group">
                            <div class="flex items-center gap-3 min-w-0">
                                <template x-if="m.avatar">
                                    <img :src="m.avatar" :alt="m.name" class="w-10 h-10 rounded-full object-cover border border-amber-500/30 shrink-0">
                                </template>
                                <template x-if="!m.avatar">
                                    <div class="w-10 h-10 rounded-full bg-slate-800 text-amber-400 font-black text-xs flex items-center justify-center border border-slate-700 shrink-0" x-text="m.initials"></div>
                                </template>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white text-sm group-hover:text-emerald-400 transition-colors truncate" x-text="m.name"></span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700" x-text="m.code"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                                        <span x-text="m.phone"></span>
                                        <span>&bull;</span>
                                        <span class="text-amber-400/90 font-medium" x-text="m.plan"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2">
                                <template x-if="m.is_inside">
                                    <span class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Inside Gym
                                    </span>
                                </template>
                                <template x-if="!m.is_inside">
                                    <button type="button"
                                            class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition-all flex items-center gap-1.5 shadow-sm group-hover:shadow-emerald-500/20">
                                        <span>⚡ Check In</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Live Feedback Alert Banner --}}
            <template x-if="feedback">
                <div class="p-4 rounded-2xl border transition-all animate-fadeIn"
                     :class="feedback.type === 'success' ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-200' : 'bg-rose-500/15 border-rose-500/30 text-rose-200'">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="text-xl" x-text="feedback.type === 'success' ? '✅' : '⚠️'"></span>
                            <div>
                                <h4 class="font-bold text-sm" :class="feedback.type === 'success' ? 'text-emerald-300' : 'text-rose-300'" x-text="feedback.title"></h4>
                                <p class="text-xs mt-0.5 text-slate-300" x-text="feedback.message"></p>
                                
                                <template x-if="feedback.member">
                                    <div class="mt-2 pt-2 border-t border-slate-700/50 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-300">
                                        <span>Member: <strong class="text-white" x-text="feedback.member.name"></strong> (<span x-text="feedback.member.code"></span>)</span>
                                        <template x-if="feedback.member.auto_checkout">
                                            <span class="text-amber-300">Auto-Checkout: <strong x-text="feedback.member.auto_checkout"></strong></span>
                                        </template>
                                        <template x-if="feedback.member.plan">
                                            <span>Plan: <strong x-text="feedback.member.plan"></strong></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="feedback = null" class="text-slate-400 hover:text-white text-xs">✕</button>
                    </div>
                </div>
            </template>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             4. LIVE ATTENDANCE STREAM & HISTORY TABLE
        ══════════════════════════════════════════════════════════════ --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
            
            <div class="p-4 sm:p-5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-sm border border-amber-500/20">
                        📋
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-white tracking-wide">Today's Live Attendance Stream</h3>
                        <p class="text-xs text-slate-400">All check-ins automatically record an 1-hour session duration</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-400">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700 font-mono text-slate-300 text-[11px]">
                        {{ now()->format('D, d M Y') }}
                    </span>
                    <span>&bull;</span>
                    <span>{{ $attendance->total() }} Log(s) Recorded</span>
                </div>
            </div>

            <!-- Mobile View: Clean Card Stream -->
            <div class="block md:hidden divide-y divide-slate-800/80">
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
                                    <img src="{{ $att->member->avatar_url }}" alt="{{ $att->member->full_name }}" class="w-10 h-10 rounded-full object-cover border border-amber-500/30 shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center font-black text-amber-400 text-xs border border-slate-700 shrink-0">
                                        {{ strtoupper(substr($att->member->first_name ?? 'M', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="font-bold text-white text-sm block truncate">{{ $att->member->full_name ?? 'Member' }}</span>
                                    <span class="text-xs text-slate-400 font-mono">{{ $att->member->member_code ?? '' }}</span>
                                </div>
                            </div>

                            @if($isInsideNow)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    On Floor
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700 shrink-0">
                                    Completed
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-bold">Check-In</span>
                                <span class="font-semibold text-emerald-400">{{ $checkInTime ? $checkInTime->format('h:i:s A') : '—' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-bold">Check-Out</span>
                                @if($att->check_out)
                                    <span class="font-medium text-slate-300">{{ \Carbon\Carbon::parse($att->check_out)->format('h:i:s A') }}</span>
                                @else
                                    <span class="font-medium text-amber-400/90">Auto: {{ $autoExitTime ? $autoExitTime->format('h:i A') : '1 hr' }}</span>
                                @endif
                            </div>
                        </div>

                        @if($isInsideNow)
                            <form action="{{ route('app.attendance.checkout', $att->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white border border-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Check Out Now (Early Exit)</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-xs">No attendance records found for today.</div>
                @endforelse
            </div>

            <!-- Desktop View: Clean Executive Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-4 px-5">Member</th>
                            <th class="py-4 px-5">Check-In Time</th>
                            <th class="py-4 px-5">Check-Out (1 Hr Policy)</th>
                            <th class="py-4 px-5">Access Method</th>
                            <th class="py-4 px-5">Status</th>
                            <th class="py-4 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($attendance as $att)
                            @php
                                $checkInTime = $att->check_in ? \Carbon\Carbon::parse($att->check_in) : null;
                                $autoExitTime = $checkInTime ? $checkInTime->copy()->addMinutes(60) : null;
                                $isInsideNow = empty($att->check_out);
                            @endphp
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                
                                {{-- Member Info --}}
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        @if($att->member?->avatar_url)
                                            <img src="{{ $att->member->avatar_url }}" alt="{{ $att->member->full_name }}" class="w-9 h-9 rounded-full object-cover border border-amber-500/30 shrink-0">
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center font-black text-amber-400 text-xs border border-slate-700 shrink-0">
                                                {{ strtoupper(substr($att->member->first_name ?? 'M', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-bold text-white text-xs block">{{ $att->member->full_name ?? 'Member' }}</span>
                                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                                <span class="font-mono">{{ $att->member->member_code ?? '' }}</span>
                                                <span>&bull;</span>
                                                <span class="text-amber-400/80">{{ $att->member->activeMembership?->plan?->name ?? 'Active' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Check-In Time --}}
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-emerald-400 text-xs">
                                            {{ $checkInTime ? $checkInTime->format('h:i:s A') : '—' }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 block">{{ $att->date ? $att->date->format('M d, Y') : '' }}</span>
                                </td>

                                {{-- Check-Out Time / 1 Hr Auto-Exit --}}
                                <td class="py-3.5 px-5">
                                    @if($att->check_out)
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-slate-300 font-medium">
                                                {{ \Carbon\Carbon::parse($att->check_out)->format('h:i:s A') }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-800 text-slate-400 font-mono">
                                                {{ $att->status === 'AUTO_CHECKOUT' ? 'Auto (1 hr)' : 'Manual' }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[11px] font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                Auto-exit: {{ $autoExitTime ? $autoExitTime->format('h:i A') : '1 hr' }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Method --}}
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700/80 inline-flex items-center gap-1">
                                        @if($att->method === 'qr')
                                            <span>📱 QR Pass</span>
                                        @elseif($att->method === 'facial')
                                            <span>👤 Face Terminal</span>
                                        @elseif($att->method === 'biometric')
                                            <span>👆 Biometric</span>
                                        @else
                                            <span>🖥️ Front Desk</span>
                                        @endif
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="py-3.5 px-5">
                                    @if($isInsideNow)
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 inline-flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                            Active Workout
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-800/80 text-slate-400 border border-slate-700/60 inline-flex items-center gap-1">
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
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-[10px] font-bold transition-all cursor-pointer">
                                                <span>Early Checkout</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-slate-500 font-mono">
                                            Logged
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <div class="text-2xl">📋</div>
                                        <div class="font-bold text-white text-sm">No Attendance Logged Today</div>
                                        <p class="text-xs text-slate-400">Scan member passes or search active members above to start recording.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendance->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $attendance->links() }}
                </div>
            @endif

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             5. CAMERA QR SCANNER MODAL
        ══════════════════════════════════════════════════════════════ --}}
        <div x-show="showScannerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
            <div @click.away="stopCameraScanner()" class="w-full max-w-md rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 text-center space-y-4 relative text-white">
                <button type="button" @click="stopCameraScanner()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto text-xl border border-emerald-500/20">
                    📷
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Live Camera QR Scanner</h3>
                    <p class="text-xs text-slate-400 mt-1">Point your camera at the member's digital QR pass</p>
                </div>
                
                <div id="camera-reader" class="w-full aspect-square max-w-[300px] mx-auto rounded-2xl overflow-hidden bg-black border border-slate-700"></div>

                <button type="button" @click="stopCameraScanner()" class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer transition-colors">
                    Close Scanner
                </button>
            </div>
        </div>

    </div>

    <!-- html5-qrcode scanner script -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</x-app-layout>
