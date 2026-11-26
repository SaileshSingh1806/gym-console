<x-guest-layout :hide-nav="true" :hide-footer="true" :title="'Gym check-in — ' . ($tenant->name ?? 'GymDeskHub')">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        .gdh-checkin { background:#0d0e10; color:#ecedef; font-family:Inter,system-ui,sans-serif; font-size:14px; line-height:1.5; min-height:100dvh; color-scheme:dark; }
        .gdh-checkin [hidden], .gdh-checkin [x-cloak] { display:none!important; }
        .gdh-checkin .checkin-card { background:#141518; border:1px solid #26282d; border-radius:12px; }
        .gdh-checkin .checkin-eyebrow { font-size:12px; color:#a0a4ab; font-weight:500; }
        .gdh-checkin .checkin-logo-fallback { width:56px; height:56px; display:grid; place-items:center; margin:auto; background:#1b1d21; border:1px solid #373a41; border-radius:10px; font-size:20px; font-weight:600; }
        .gdh-checkin .checkin-input { min-height:48px; background:#0d0e10; color:#ecedef; border:1px solid #373a41; border-radius:7px; font-size:16px; }
        .gdh-checkin .checkin-input::placeholder { color:#a0a4ab; font-size:14px; }
        .gdh-checkin .checkin-button { min-height:46px; display:flex; align-items:center; justify-content:center; gap:6px; border-radius:7px; padding:10px 14px; font-size:14px; font-weight:600; cursor:pointer; }
        .gdh-checkin .checkin-primary { background:#f5a524; color:#16130b; border:1px solid transparent; }
        .gdh-checkin .checkin-primary:hover { background:#e09414; }
        .gdh-checkin .checkin-secondary { background:#1b1d21; color:#ecedef; border:1px solid #373a41; }
        .gdh-checkin .checkin-secondary:hover { background:#26282d; }
        .gdh-checkin .checkin-button:disabled { opacity:.65; cursor:wait; }
        .gdh-checkin :is(a,button,input):focus-visible { outline:2px solid #f5a524; outline-offset:3px; }
        @media(max-width:360px) { .gdh-checkin .checkin-card { padding:16px; } .gdh-checkin .checkin-button { padding-inline:8px; } }
    </style>
    <div class="gdh-checkin min-h-screen flex flex-col items-center px-5">
        
        <!-- Top Header Navigation -->
        <header class="w-full max-w-[920px] flex items-center justify-between gap-4 py-5 border-b border-[#26282d] text-sm">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-[#a0a4ab] hover:text-white transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>GymDeskHub</span>
            </a>

            @auth
                <a href="{{ route('app.portal') }}" class="text-[#f5a524] hover:underline font-medium flex items-center gap-1">
                    <span>Member portal</span> &rarr;
                </a>
            @else
                <a href="{{ route('login', ['role' => 'member']) }}" class="text-[#a0a4ab] hover:text-[#f5a524] transition-colors font-medium">
                    Member login &rarr;
                </a>
            @endauth
        </header>

        <!-- Centered Attendance Card -->
        <main class="w-full max-w-[440px] my-auto py-8" x-data="{ action: 'check_in', submitting: false }">
            <div class="checkin-card p-5 sm:p-7 space-y-5">
                
                <!-- Gym Branding -->
                <div class="text-center space-y-3">
                    @php
                        $rawLogo = $tenant->logo_url ?? null;
                        $gymLogo = null;
                        if (!empty($rawLogo)) {
                            if (str_starts_with($rawLogo, 'http://') || str_starts_with($rawLogo, 'https://')) {
                                $gymLogo = $rawLogo;
                            } elseif (str_starts_with($rawLogo, '/storage/') || str_starts_with($rawLogo, 'storage/')) {
                                $gymLogo = asset(ltrim($rawLogo, '/'));
                            } else {
                                $gymLogo = asset('storage/' . $rawLogo);
                            }
                        }
                    @endphp
                    @if(!empty($gymLogo))
                        <img src="{{ $gymLogo }}" alt="{{ $tenant->name }}" class="h-14 sm:h-16 w-auto max-w-[200px] mx-auto object-contain" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                        <div class="checkin-logo-fallback" hidden>{{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}</div>
                    @else
                        <div class="checkin-logo-fallback">
                            {{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}
                        </div>
                    @endif

                    <div>
                        <p class="checkin-eyebrow">Member attendance</p>
                        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight mt-1">
                            {{ $tenant->name }}
                        </h1>
                        <p class="text-sm text-[#a0a4ab] mt-1">
                            {{ $tenant->phone ? ('Contact: ' . $tenant->phone) : 'Record your gym visit' }}
                        </p>
                    </div>
                </div>

                <!-- Flash / Validation Errors -->
                @if(session('error'))
                    <div class="p-3.5 rounded-lg bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-medium leading-relaxed">{{ session('error') }}</span>
                    </div>
                @endif

                @if($status === 'success' && $member)
                    <!-- SUCCESS CARD -->
                    <div class="p-5 sm:p-6 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-center space-y-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500 flex items-center justify-center mx-auto text-3xl ">
                            ✓
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-emerald-400 uppercase tracking-wider">Check-in recorded</span>
                            <h2 class="text-xl font-semibold text-white mt-0.5">{{ $member->full_name }}</h2>
                            <p class="text-xs text-[#a0a4ab] font-mono">{{ $member->member_code }}</p>
                        </div>

                        <div class="p-3 rounded-lg bg-[#1b1d21] border border-[#26282d] text-xs space-y-2 text-left">
                            <div class="flex justify-between items-center">
                                <span class="text-[#a0a4ab]">Time:</span>
                                <span class="font-medium text-emerald-400">{{ now()->format('h:i:s A, d M Y') }}</span>
                            </div>
                            @php
                                $activePlan = $member->activeMembership?->plan;
                                $endDate = $member->activeMembership?->end_date;
                                $daysLeft = $endDate ? (int) now()->startOfDay()->diffInDays($endDate->startOfDay(), false) : null;
                            @endphp
                            @if($activePlan)
                                <div class="flex justify-between items-center border-t border-[#26282d] pt-2">
                                    <span class="text-[#a0a4ab]">Membership:</span>
                                    <span class="font-medium text-white">{{ $activePlan->name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#a0a4ab]">Validity:</span>
                                    <span class="font-medium text-amber-400">{{ $daysLeft !== null && $daysLeft >= 0 ? ($daysLeft . ' Days Left') : 'Active' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="pt-2 space-y-2">
                            <a href="{{ route('app.portal') }}" class="checkin-button checkin-primary w-full">
                                <span>Open member portal</span> &rarr;
                            </a>
                        </div>
                    </div>

                @elseif($status === 'checkout' && $member)
                    <!-- CHECKOUT CARD -->
                    <div class="p-5 sm:p-6 rounded-lg bg-amber-500/10 border border-amber-500/30 text-center space-y-4">
                        <div class="w-16 h-16 rounded-full bg-amber-500/20 text-amber-400 border-2 border-amber-500 flex items-center justify-center mx-auto text-2xl">
                            ✓
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-amber-400 uppercase tracking-wider">Check-out recorded</span>
                            <h2 class="text-xl font-semibold text-white mt-0.5">{{ $member->full_name }}</h2>
                            <p class="text-xs text-[#a0a4ab]">Your visit has been recorded.</p>
                        </div>
                        <div class="p-3 rounded-lg bg-[#1b1d21] border border-[#26282d] text-xs flex justify-between items-center">
                            <span class="text-[#a0a4ab]">Out Time:</span>
                            <span class="font-medium text-amber-400">{{ now()->format('h:i:s A, d M Y') }}</span>
                        </div>
                    </div>

                @elseif($status === 'expired' && $member)
                    <!-- EXPIRED NOTICE -->
                    <div class="p-5 sm:p-6 rounded-lg bg-rose-500/10 border border-rose-500/30 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center justify-center mx-auto text-2xl">
                            !
                        </div>
                        <h2 class="text-lg font-semibold text-white">{{ $member->full_name }}</h2>
                        <p class="text-xs text-rose-400 leading-relaxed font-semibold">
                            {{ $message }}
                        </p>
                        <p class="text-[11px] text-[#a0a4ab]">
                            Please contact front desk or reception to renew your membership and regain workout access.
                        </p>
                    </div>

                @else
                    <!-- INPUT FORM: Check In / Check Out -->
                    <form action="{{ route('gym.checkin.process', ['slug' => $slug]) }}" method="POST" @submit="submitting = true" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action" :value="action">

                        <div class="space-y-1.5">
                            <label for="identifier" class="block text-sm font-medium text-[#ecedef]">
                                Mobile number or member ID
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#a0a4ab]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </div>
                                <input type="text" 
                                       id="identifier"
                                       name="identifier" 
                                       value="{{ old('identifier') }}" 
                                       required autocomplete="off" autocapitalize="none" spellcheck="false" 
                                       placeholder="Enter mobile number or member ID" 
                                       class="checkin-input w-full pl-10 pr-4">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button type="submit" 
                                    @click="action = 'check_in'"
                                    :disabled="submitting"
                                    class="checkin-button checkin-primary">
                                <span x-cloak x-show="submitting && action === 'check_in'">Recording…</span>
                                <span x-show="!submitting || action !== 'check_in'">Check in</span>
                            </button>

                            <button type="submit" 
                                    @click="action = 'check_out'"
                                    :disabled="submitting"
                                    class="checkin-button checkin-secondary">
                                <span x-cloak x-show="submitting && action === 'check_out'">Recording…</span>
                                <span x-show="!submitting || action !== 'check_out'">Check out</span>
                            </button>
                        </div>
                    </form>
                @endif

                <!-- Bottom Links -->
                <div class="pt-4 border-t border-[#26282d] text-center space-y-2">
                    <p class="text-xs text-[#a0a4ab]">
                        View your membership, workouts and invoices.
                    </p>
                    <a href="{{ route('login', ['role' => 'member']) }}" class="inline-flex items-center gap-1.5 text-xs text-[#f5a524] hover:underline font-medium">
                        <span>Sign in to member portal</span> &rarr;
                    </a>
                </div>
            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="w-full max-w-[920px] flex flex-wrap items-center justify-between gap-2 text-xs text-[#a0a4ab] py-5 border-t border-[#26282d]">
            <span>Powered by <strong>GymDeskHub</strong></span>
            <span>Member attendance</span>
        </footer>

    </div>
</x-guest-layout>

