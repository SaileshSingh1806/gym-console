<x-guest-layout :hide-nav="true" :hide-footer="true" title="Gym Check-in — {{ $tenant->name ?? 'GymDeskHub' }}">
    <div class="min-h-screen flex flex-col justify-between items-center py-6 sm:py-10 px-4 relative overflow-hidden bg-[#080c14] text-slate-100 selection:bg-emerald-500/25 selection:text-emerald-200">
        
        <!-- Ambient Background Glow -->
        <div class="absolute inset-0 bg-subtle-grid opacity-20 pointer-events-none"></div>
        <div class="absolute w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-3xl -top-32 left-1/2 -translate-x-1/2 pointer-events-none"></div>

        <!-- Top Header Navigation -->
        <header class="w-full max-w-md flex items-center justify-between relative z-10 text-xs">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-white transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>GymDeskHub</span>
            </a>

            @auth
                <a href="{{ route('app.portal') }}" class="text-emerald-400 hover:underline font-bold flex items-center gap-1">
                    <span>My Member Portal</span> &rarr;
                </a>
            @else
                <a href="{{ route('login', ['role' => 'member']) }}" class="text-slate-400 hover:text-emerald-400 transition-colors font-medium">
                    Member Login &rarr;
                </a>
            @endauth
        </header>

        <!-- Centered Attendance Card -->
        <main class="w-full max-w-[440px] my-auto py-6 relative z-10" x-data="{ action: 'check_in', submitting: false }">
            <div class="p-6 sm:p-9 rounded-3xl bg-[#0c1220]/95 border border-slate-800/90 shadow-2xl shadow-black/80 backdrop-blur-xl space-y-6">
                
                <!-- Gym Branding -->
                <div class="text-center space-y-3">
                    @if(!empty($tenant->logo_url))
                        <img src="{{ asset('storage/' . $tenant->logo_url) }}" alt="{{ $tenant->name }}" class="h-14 sm:h-16 w-auto max-w-[200px] mx-auto object-contain drop-shadow-md">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center text-slate-950 font-black text-2xl mx-auto shadow-lg shadow-emerald-500/20">
                            {{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}
                        </div>
                    @endif

                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[11px] font-black uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Gym Entrance Scan &amp; Check-In</span>
                        </span>
                        <h1 class="text-2xl font-black text-white tracking-tight mt-2">
                            {{ $tenant->name }}
                        </h1>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $tenant->phone ? ('Contact: ' . $tenant->phone) : 'Fast automated attendance check-in' }}
                        </p>
                    </div>
                </div>

                <!-- Flash / Validation Errors -->
                @if(session('error'))
                    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-medium leading-relaxed">{{ session('error') }}</span>
                    </div>
                @endif

                @if($status === 'success' && $member)
                    <!-- SUCCESS CARD -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-center space-y-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500 flex items-center justify-center mx-auto text-3xl animate-bounce">
                            ✓
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Check-in Confirmed</span>
                            <h2 class="text-xl font-black text-white mt-0.5">{{ $member->full_name }}</h2>
                            <p class="text-xs text-slate-400 font-mono">{{ $member->member_code }}</p>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-xs space-y-2 text-left">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Time:</span>
                                <span class="font-bold text-emerald-400">{{ now()->format('h:i:s A, d M Y') }}</span>
                            </div>
                            @php
                                $activePlan = $member->activeMembership?->plan;
                                $endDate = $member->activeMembership?->end_date;
                                $daysLeft = $endDate ? (int) now()->startOfDay()->diffInDays($endDate->startOfDay(), false) : null;
                            @endphp
                            @if($activePlan)
                                <div class="flex justify-between items-center border-t border-slate-800/80 pt-2">
                                    <span class="text-slate-400">Membership:</span>
                                    <span class="font-bold text-white">{{ $activePlan->name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400">Validity:</span>
                                    <span class="font-bold text-amber-400">{{ $daysLeft !== null && $daysLeft >= 0 ? ($daysLeft . ' Days Left') : 'Active' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="pt-2 space-y-2">
                            <a href="{{ route('app.portal') }}" class="w-full py-3 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 cursor-pointer">
                                <span>Open My Full Member Portal</span> &rarr;
                            </a>
                        </div>
                    </div>

                @elseif($status === 'checkout' && $member)
                    <!-- CHECKOUT CARD -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-center space-y-4">
                        <div class="w-16 h-16 rounded-full bg-amber-500/20 text-amber-400 border-2 border-amber-500 flex items-center justify-center mx-auto text-2xl">
                            👋
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Check-out Recorded</span>
                            <h2 class="text-xl font-black text-white mt-0.5">{{ $member->full_name }}</h2>
                            <p class="text-xs text-slate-400">Great workout today! See you next time.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-xs flex justify-between items-center">
                            <span class="text-slate-400">Out Time:</span>
                            <span class="font-bold text-amber-400">{{ now()->format('h:i:s A, d M Y') }}</span>
                        </div>
                    </div>

                @elseif($status === 'expired' && $member)
                    <!-- EXPIRED NOTICE -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center justify-center mx-auto text-2xl">
                            ⚠️
                        </div>
                        <h2 class="text-lg font-black text-white">{{ $member->full_name }}</h2>
                        <p class="text-xs text-rose-400 leading-relaxed font-semibold">
                            {{ $message }}
                        </p>
                        <p class="text-[11px] text-slate-400">
                            Please contact front desk or reception to renew your membership and regain workout access.
                        </p>
                    </div>

                @else
                    <!-- INPUT FORM: Check In / Check Out -->
                    <form action="{{ route('gym.checkin.process', ['slug' => $slug]) }}" method="POST" @submit="submitting = true" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action" :value="action">

                        <div class="space-y-1.5">
                            <label for="identifier" class="block text-xs font-semibold text-slate-300">
                                Enter Mobile Number or Member ID
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </div>
                                <input type="text" 
                                       id="identifier"
                                       name="identifier" 
                                       value="{{ old('identifier') }}" 
                                       required 
                                       autofocus 
                                       placeholder="e.g. 9876543210 or MEM001" 
                                       class="w-full pl-10 pr-4 py-3.5 rounded-xl bg-[#080d17] border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 transition-all font-mono">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button type="submit" 
                                    @click="action = 'check_in'"
                                    :disabled="submitting"
                                    class="py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:brightness-110 active:scale-[0.99] text-white font-black text-xs shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5">
                                <span x-show="submitting && action === 'check_in'" class="animate-spin text-sm">⏳</span>
                                <span x-show="!submitting || action !== 'check_in'">⚡ Check In</span>
                            </button>

                            <button type="submit" 
                                    @click="action = 'check_out'"
                                    :disabled="submitting"
                                    class="py-3.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white font-bold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 border border-slate-700/80">
                                <span x-show="submitting && action === 'check_out'" class="animate-spin text-sm">⏳</span>
                                <span x-show="!submitting || action !== 'check_out'">👋 Check Out</span>
                            </button>
                        </div>
                    </form>
                @endif

                <!-- Bottom Links -->
                <div class="pt-4 border-t border-slate-800/80 text-center space-y-2">
                    <p class="text-xs text-slate-400">
                        Want to view your full diet, workouts &amp; invoices?
                    </p>
                    <a href="{{ route('login', ['role' => 'member']) }}" class="inline-flex items-center gap-1.5 text-xs text-emerald-400 hover:underline font-bold">
                        <span>Sign In to Member Portal</span> &rarr;
                    </a>
                </div>
            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="w-full max-w-md flex items-center justify-between text-[11px] text-slate-500 relative z-10 pt-2">
            <span>Powered by <strong>GymDeskHub</strong></span>
            <span>🔒 Secure QR Access</span>
        </footer>

    </div>
</x-guest-layout>

