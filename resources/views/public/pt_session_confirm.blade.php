<x-guest-layout :hide-nav="true" :hide-footer="true" title="Confirm PT Session — {{ $tenant->name ?? 'GymDeskHub' }}">
    <div class="min-h-screen flex flex-col justify-between items-center py-6 sm:py-10 px-4 relative overflow-hidden bg-[#080c14] text-slate-100 selection:bg-amber-500/25 selection:text-amber-200">
        
        <!-- Ambient Background Glow (Amber & Navy theme) -->
        <div class="absolute inset-0 bg-subtle-grid opacity-15 pointer-events-none"></div>
        <div class="absolute w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl -top-32 left-1/2 -translate-x-1/2 pointer-events-none"></div>
        <div class="absolute w-[400px] h-[400px] bg-indigo-500/10 rounded-full blur-3xl bottom-0 right-0 pointer-events-none"></div>

        <!-- Top Header Navigation -->
        <header class="w-full max-w-md flex items-center justify-between relative z-10 text-xs">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-white transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>GymDeskHub</span>
            </a>

            @auth
                <a href="{{ route('app.portal') }}" class="text-amber-400 hover:underline font-bold flex items-center gap-1">
                    <span>Member Portal</span> &rarr;
                </a>
            @else
                <a href="{{ route('login', ['role' => 'member', 'redirect' => url()->current()]) }}" class="text-slate-400 hover:text-amber-400 transition-colors font-medium">
                    Member Login &rarr;
                </a>
            @endauth
        </header>

        <!-- Main Confirmation Container -->
        <main class="w-full max-w-[460px] my-auto py-6 relative z-10" x-data="{
            showIssueBox: false,
            submitting: false,
            confirmed: {{ in_array($session->status, ['CONFIRMED', 'COMPLETED']) ? 'true' : 'false' }},
            issueReported: {{ $session->status === 'ISSUE_REPORTED' ? 'true' : 'false' }},
            issueText: '',
            phoneInput: '',
            otpInput: '',
            rating: 5,
            memberFeedback: '',
            feelingTags: ['Great Pump 💪', 'Intense 🔥', 'Felt Good 😊', 'Need Recovery 😴'],
            errorMessage: '{{ session('error') ?? '' }}',
            successMessage: '{{ session('success') ?? '' }}',
            remainingSessions: {{ $package?->remaining_sessions ?? 0 }},
            async confirmSession() {
                if (this.submitting || this.confirmed) return;
                this.submitting = true;
                this.errorMessage = '';
                try {
                    const res = await fetch('{{ route('pt-session.confirm.submit', ['token' => $session->confirmation_token]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            phone_verification: this.phoneInput,
                            otp_code: this.otpInput,
                            rating: this.rating,
                            member_feedback: this.memberFeedback
                        })
                    });
                    const data = await res.json();
                    if (data && data.success) {
                        this.confirmed = true;
                        this.successMessage = data.message;
                        if (data.remaining_sessions !== undefined) {
                            this.remainingSessions = data.remaining_sessions;
                        }
                    } else {
                        this.errorMessage = data.message || 'Confirmation failed. Please try again.';
                    }
                } catch (e) {
                    this.$refs.fallbackConfirmForm?.submit();
                } finally {
                    this.submitting = false;
                }
            },
            async reportIssue() {
                if (!this.issueText || this.issueText.trim().length < 4) {
                    this.errorMessage = 'Please describe the issue in at least 4 characters.';
                    return;
                }
                this.submitting = true;
                this.errorMessage = '';
                try {
                    const res = await fetch('{{ route('pt-session.confirm.issue', ['token' => $session->confirmation_token]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            notes: this.issueText
                        })
                    });
                    const data = await res.json();
                    if (data && data.success) {
                        this.issueReported = true;
                        this.successMessage = data.message;
                    } else {
                        this.errorMessage = data.message || 'Failed to submit issue.';
                    }
                } catch (e) {
                    this.$refs.fallbackIssueForm?.submit();
                } finally {
                    this.submitting = false;
                }
            }
        }">
            <div class="p-6 sm:p-8 rounded-3xl bg-[#0c1220]/95 border border-slate-800/90 shadow-2xl shadow-black/90 backdrop-blur-xl space-y-5">
                
                <!-- Gym Branding -->
                <div class="text-center space-y-2.5">
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
                        <img src="{{ $gymLogo }}" alt="{{ $tenant->name }}" class="h-12 sm:h-14 w-auto max-w-[180px] mx-auto object-contain drop-shadow-md" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-600 flex items-center justify-center text-slate-950 font-black text-xl mx-auto shadow-lg shadow-amber-500/20\'>{{ strtoupper(substr($tenant->name ?: \'G\', 0, 2)) }}</div>';">
                    @else
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-600 flex items-center justify-center text-slate-950 font-black text-xl mx-auto shadow-lg shadow-amber-500/20">
                            {{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}
                        </div>
                    @endif

                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[11px] font-black uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <span>Personal Training Confirmation</span>
                        </span>
                        <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1.5">
                            {{ $tenant->name }}
                        </h1>
                        <p class="text-xs text-slate-400">
                            {{ $session->branch?->name ?? 'Gym Center' }}
                        </p>
                    </div>
                </div>

                <!-- Error Alert -->
                <template x-if="errorMessage">
                    <div class="p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-start gap-2.5 animate-shake">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-medium leading-relaxed" x-text="errorMessage"></span>
                    </div>
                </template>

                <!-- STATE 1: ALREADY CONFIRMED -->
                <template x-if="confirmed">
                    <div class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-center space-y-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500/60 flex items-center justify-center mx-auto text-3xl font-black shadow-lg shadow-emerald-500/20">
                            ✓
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Session Verified &amp; Confirmed</span>
                            <h2 class="text-lg font-black text-white mt-1">{{ $member->full_name }}</h2>
                            <p class="text-xs text-slate-300 mt-1" x-text="successMessage || 'This session has been confirmed and 1 session was deducted.'"></p>
                        </div>

                        <!-- Balance Card -->
                        <div class="p-3.5 rounded-xl bg-[#080c14] border border-slate-800 text-left space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Package:</span>
                                <span class="font-bold text-white">{{ $package?->package_name ?? 'PT Package' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Trainer:</span>
                                <span class="font-bold text-amber-400">{{ $trainer?->full_name ?? 'Assigned Trainer' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-800">
                                <span class="text-slate-400">Remaining Balance:</span>
                                <span class="font-black text-emerald-400 text-sm" x-text="remainingSessions + ' Sessions Left'"></span>
                            </div>
                        </div>

                        <a href="{{ route('app.portal') }}" class="inline-flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all">
                            <span>Open My Member Portal</span> &rarr;
                        </a>
                    </div>
                </template>

                <!-- STATE 2: ISSUE REPORTED -->
                <template x-if="issueReported">
                    <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500 flex items-center justify-center mx-auto text-2xl font-bold">
                            ⚠️
                        </div>
                        <h2 class="text-base font-black text-amber-300">Issue Reported to Gym Manager</h2>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Your report has been logged with management. <strong class="text-white">Your PT package balance was NOT deducted.</strong>
                        </p>
                        @if($session->issue_notes)
                            <div class="p-3 rounded-lg bg-black/40 border border-slate-800 text-xs text-slate-400 text-left italic">
                                &ldquo;{{ $session->issue_notes }}&rdquo;
                            </div>
                        @endif
                        <a href="{{ route('app.portal') }}" class="inline-block mt-2 text-xs text-amber-400 hover:underline font-bold">
                            Return to Member Portal &rarr;
                        </a>
                    </div>
                </template>

                <!-- STATE 3: EXPIRED QR CODE -->
                @if($isExpired && $session->status === 'PENDING')
                    <div class="p-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500 flex items-center justify-center mx-auto text-2xl font-bold">
                            ⏱️
                        </div>
                        <h2 class="text-base font-black text-rose-300">QR Code Expired</h2>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            This confirmation QR code expired for your security. Please ask your trainer to tap <strong class="text-white">&ldquo;Refresh QR&rdquo;</strong> to get a fresh code.
                        </p>
                    </div>
                @endif

                <!-- STATE 4: PENDING CONFIRMATION DETAILS -->
                <div x-show="!confirmed && !issueReported && !{{ $isExpired ? 'true' : 'false' }}" class="space-y-4">
                    
                    <!-- Session Summary Card -->
                    <div class="p-4 rounded-2xl bg-[#080c14]/90 border border-slate-800 space-y-3">
                        
                        <!-- Member Row -->
                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800/80">
                            <span class="text-[11px] font-semibold text-slate-400">Member:</span>
                            <div class="text-right">
                                <span class="font-bold text-white text-sm">{{ $member->full_name }}</span>
                                <span class="block text-[10px] text-slate-500">{{ substr($member->phone, 0, 2) }}******{{ substr($member->phone, -2) }}</span>
                            </div>
                        </div>

                        <!-- Trainer Row -->
                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800/80">
                            <span class="text-[11px] font-semibold text-slate-400">Trainer:</span>
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 text-[10px] font-bold flex items-center justify-center">
                                    {{ substr($trainer?->first_name ?? 'T', 0, 1) }}
                                </div>
                                <span class="font-bold text-amber-400 text-xs">{{ $trainer?->full_name ?? 'Assigned Trainer' }}</span>
                            </div>
                        </div>

                        <!-- Date & Duration -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                <span class="text-[10px] text-slate-400 block">Date &amp; Time</span>
                                <span class="font-bold text-white text-[11px]">{{ $session->session_date->format('d M Y') }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                <span class="text-[10px] text-slate-400 block">Duration &amp; Focus</span>
                                <span class="font-bold text-white text-[11px]">{{ $session->duration_minutes }} Minutes</span>
                                <span class="text-[10px] text-amber-400 truncate block">{{ $session->focus_area ?? 'Workout' }}</span>
                            </div>
                        </div>

                        <!-- Package Balance Impact -->
                        <div class="p-3 rounded-xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-300 text-[11px]">PT Package:</span>
                                <span class="font-bold text-white">{{ $package?->package_name ?? 'Personal Training' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-1.5 pt-1.5 border-t border-amber-500/20">
                                <span class="text-slate-400 text-[11px]">Current Balance:</span>
                                <span class="font-bold text-slate-200">{{ $package?->remaining_sessions }} left</span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-1">
                                <span class="text-amber-300 font-semibold text-[11px]">After This Session:</span>
                                <span class="font-black text-amber-400 text-sm">{{ max(0, ($package?->remaining_sessions ?? 1) - 1) }} left (-1)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Member Auth / Verification Verification Status -->
                    @if($currentUser && $wrongUserLoggedIn)
                        <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1.5">
                            <p class="font-bold">Logged in as: {{ $currentUser->name }}</p>
                            <p class="text-[11px] text-slate-300 leading-tight">
                                This session is assigned to <strong class="text-white">{{ $member->full_name }}</strong>. Please log in with the correct account or confirm with your phone number below.
                            </p>
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="inline-block text-[11px] text-amber-400 font-bold underline">
                                Switch Account &rarr;
                            </a>
                        </div>
                    @elseif($currentUser && $isAssignedMember)
                        <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Verified as <strong class="text-white">{{ $member->full_name }}</strong></span>
                        </div>
                    @else
                        <!-- Fast Phone Verification for guests -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-semibold text-slate-300">
                                Confirm Your Registered Phone Number:
                            </label>
                            <input type="tel" 
                                   x-model="phoneInput" 
                                   placeholder="e.g. {{ substr($member->phone, 0, 2) }}******{{ substr($member->phone, -2) }}" 
                                   class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            <p class="text-[10px] text-slate-500">Or use Session PIN <span class="text-amber-400 font-mono font-bold">{{ $session->otp_code }}</span> shown on trainer screen.</p>
                        </div>
                    @endif

                    <!-- Quick Coach Rating & Feedback (1-Tap) -->
                    <div class="p-3.5 rounded-2xl bg-[#080c14]/90 border border-slate-800 space-y-2 text-center">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-300">Rate Your PT Session</span>
                            <span class="text-[10px] text-amber-400 font-bold" x-text="rating + ' / 5 Stars'"></span>
                        </div>
                        <div class="flex items-center justify-center gap-2 py-0.5">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button type="button" 
                                        @click="rating = star" 
                                        class="text-2xl transition-transform hover:scale-125 focus:outline-none cursor-pointer"
                                        :class="star <= rating ? 'text-amber-400' : 'text-slate-700'">
                                    ★
                                </button>
                            </template>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-1.5 pt-1">
                            <template x-for="tag in feelingTags" :key="tag">
                                <button type="button" 
                                        @click="memberFeedback = (memberFeedback === tag ? '' : tag)"
                                        :class="memberFeedback === tag ? 'bg-amber-500 text-slate-950 font-bold border-amber-500 shadow-sm' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white'"
                                        class="px-2.5 py-1 rounded-lg text-[10px] border transition-all cursor-pointer">
                                    <span x-text="tag"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Primary Action: Confirm Session -->
                    <div class="space-y-2 pt-1">
                        <button type="button" 
                                @click="confirmSession()" 
                                :disabled="submitting || confirmed" 
                                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm tracking-wide shadow-xl shadow-amber-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <template x-if="!submitting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="submitting">
                                <svg class="w-4 h-4 animate-spin text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </template>
                            <span x-text="submitting ? 'Confirming...' : 'Confirm PT Session'">Confirm PT Session</span>
                        </button>

                        <!-- Secondary Action: Report an Issue -->
                        <div class="text-center pt-2">
                            <button type="button" 
                                    @click="showIssueBox = !showIssueBox" 
                                    class="text-xs text-slate-400 hover:text-amber-400 transition-colors font-medium flex items-center justify-center gap-1 mx-auto cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Report an issue with this session</span>
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Issue Box -->
                    <div x-show="showIssueBox" x-cloak class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 space-y-2 mt-2">
                        <label class="block text-[11px] font-bold text-amber-300">
                            Describe Issue (Balance will NOT be deducted):
                        </label>
                        <textarea x-model="issueText" 
                                  rows="3" 
                                  placeholder="e.g. Trainer was not present / Wrong member / Session duration incorrect..."
                                  class="w-full p-2.5 rounded-lg bg-black/60 border border-slate-700 text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"></textarea>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" @click="showIssueBox = false" class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700">Cancel</button>
                            <button type="button" @click="reportIssue()" :disabled="submitting || !issueText.trim()" class="px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow disabled:opacity-50">Submit Report</button>
                        </div>
                    </div>

                    <!-- Hidden Fallback Forms for non-JS browsers -->
                    <form x-ref="fallbackConfirmForm" action="{{ route('pt-session.confirm.submit', ['token' => $session->confirmation_token]) }}" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="phone_verification" :value="phoneInput">
                        <input type="hidden" name="otp_code" :value="otpInput">
                    </form>
                    <form x-ref="fallbackIssueForm" action="{{ route('pt-session.confirm.issue', ['token' => $session->confirmation_token]) }}" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="notes" :value="issueText">
                    </form>
                </div>

                <!-- Footer Security Stamp -->
                <div class="pt-2 text-center text-[10px] text-slate-500 flex items-center justify-center gap-1.5 border-t border-slate-800/60">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Secure Encrypted PT Verification &bull; {{ $tenant->name }}</span>
                </div>
            </div>
        </main>

        <footer class="text-center text-[11px] text-slate-500 relative z-10 py-2">
            &copy; {{ date('Y') }} GymDeskHub &bull; All rights reserved.
        </footer>
    </div>
</x-guest-layout>

