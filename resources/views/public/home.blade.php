<x-guest-layout 
    title="GymDeskHub™ — All-in-One Gym Management Software & Biometric Integration"
    meta-description="Manage your gym members, attendance, fee collections, and trainer workouts from one simple platform. Plug & play biometric integration and automated WhatsApp fee reminders."
    meta-keywords="gym management software, best gym software india, gym membership software, biometric integration, gym attendance system, trainer app, member app, gym billing pos, whatsapp gym reminders"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (CLEAN MATTE DARK - NO GLOW - HUMAN TONE)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero relative pt-12 pb-16 sm:pt-20 sm:pb-24 lg:pt-24 lg:pb-28 overflow-hidden bg-[#0D0E10]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                
                {{-- Category Pill Badge (Responsive Wrap for Small Screens) --}}

                {{-- Direct, Human Headline --}}
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-semibold tracking-tight text-white leading-[1.14] mb-6">
                Run your gym. From one workspace.
            </h1>

                {{-- Grounded, Straightforward Subheadline --}}
                <p class="text-base sm:text-lg lg:text-xl text-slate-300 leading-relaxed max-w-3xl mx-auto font-normal mb-10">
                    Manage memberships, attendance, payments and coaching from one connected platform. Give your staff the tools they need and keep your business in view.
                </p>

                {{-- Direct CTA Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-base transition-colors active:scale-[0.98]">
                        <span>Start Free Forever</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('contact') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 font-semibold text-base hover:bg-slate-800 hover:text-white transition-colors">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z"/></svg>
                        <span>Book a Live Demo</span>
                    </a>
                </div>

                {{-- Honest Trust Proof Points --}}
                <div class="mt-12 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-xs sm:text-sm text-slate-400 font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Free Forever Plan (Up to 75 members)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>No credit card required to start</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Plug &amp; Play Biometric Integration</span>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════
                 THE 3 CORE APPS SHOWCASE (OWNER, TRAINER, MEMBER)
            ══════════════════════════════════════════════════════════════ --}}
            <div class="mt-16 max-w-6xl mx-auto" x-data="{ activeApp: 'owner' }">
                
                {{-- App Switcher Tabs (Responsive Scrollable Container) --}}
                <div class="flex items-center justify-start sm:justify-center mb-6 overflow-x-auto max-w-full pb-2 scrollbar-none">
                    <div class="inline-flex p-1.5 rounded-lg bg-slate-900 border border-slate-800 shrink-0">
                        <button type="button" 
                                @click="activeApp = 'owner'" 
                                :class="activeApp === 'owner' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white font-medium'"
                                class="flex items-center gap-2 px-3.5 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-colors cursor-pointer whitespace-nowrap">
                            
                            <span>Owner Console</span>
                        </button>
                        <button type="button" 
                                @click="activeApp = 'trainer'" 
                                :class="activeApp === 'trainer' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white font-medium'"
                                class="flex items-center gap-2 px-3.5 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-colors cursor-pointer whitespace-nowrap">
                            
                            <span>Trainer App</span>
                        </button>
                        <button type="button" 
                                @click="activeApp = 'member'" 
                                :class="activeApp === 'member' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white font-medium'"
                                class="flex items-center gap-2 px-3.5 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-colors cursor-pointer whitespace-nowrap">
                            
                            <span>Member Pass</span>
                        </button>
                    </div>
                </div>

                {{-- APP 1: OWNER CONSOLE --}}
                <div x-show="activeApp === 'owner'" x-cloak class="rounded-xl bg-[#1B1D21] p-4 sm:p-6 border border-slate-800">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                        <div class="lg:col-span-5 space-y-4">
                            <span class="px-3 py-1 rounded-md bg-amber-500/10 text-amber-400 text-xs font-bold uppercase tracking-normal">Owner Dashboard</span>
                            <h3 class="text-2xl sm:text-3xl font-semibold text-white">Full Control Over Every Rupee &amp; Check-in</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Know exactly how many members visited today, who has an overdue payment, and how much cash vs UPI was collected at the front desk.
                            </p>
                            <ul class="space-y-2.5 text-xs sm:text-sm text-slate-300 pt-1">
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Front-desk POS: Split Cash + UPI with 1-click GST bill</li>
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Biometric Integration: Access gate locks if plan is expired</li>
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Single login to manage 1 branch or multiple locations</li>
                            </ul>
                        </div>
                        <div class="lg:col-span-7 bg-[#141518] rounded-lg p-4 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                    <span class="font-bold text-white">Downtown Branch &bull; Live Status</span>
                                </div>
                                <span class="font-mono text-emerald-400 text-[11px]">Biometric Devices Connected</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2.5">
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Today Collected</div>
                                    <div class="text-lg font-bold text-white mt-0.5">₹48,500</div>
                                    <div class="text-[10px] text-emerald-400 font-medium">Cash + UPI Tallied</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Currently Inside</div>
                                    <div class="text-lg font-bold text-amber-400 mt-0.5">38 Members</div>
                                    <div class="text-[10px] text-slate-400">Live Check-ins</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Pending Renewals</div>
                                    <div class="text-lg font-bold text-blue-400 mt-0.5">14 Members</div>
                                    <div class="text-[10px] text-emerald-400 font-medium">WhatsApp Sent</div>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                                <div class="text-[11px] font-semibold text-slate-300">Live Attendance Log:</div>
                                <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                    <span class="font-medium text-white">Rohit Sharma (12-Month Plan)</span>
                                    <span class="font-mono text-emerald-400 font-semibold">Gate Entry &bull; Checked In</span>
                                </div>
                                <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-rose-500/10 border border-rose-500/20">
                                    <span class="font-medium text-white">Ananya Sen (Plan Expired Yesterday)</span>
                                    <span class="font-mono text-rose-400 font-semibold">Entry Blocked &bull; Pay Link Sent</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- APP 2: TRAINER APP --}}
                <div x-show="activeApp === 'trainer'" x-cloak class="rounded-xl bg-[#1B1D21] p-4 sm:p-6 border border-slate-800">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                        <div class="lg:col-span-5 space-y-4">
                            <span class="px-3 py-1 rounded-md bg-emerald-500/10 text-emerald-400 text-xs font-bold uppercase tracking-normal">Trainer App</span>
                            <h3 class="text-2xl sm:text-3xl font-semibold text-white">Workout &amp; Diet Plans on Trainer’s Phone</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                No more writing diet charts on loose paper. Trainers assign customized exercises, sets, reps, and macro targets directly to their assigned clients.
                            </p>
                            <ul class="space-y-2.5 text-xs sm:text-sm text-slate-300 pt-1">
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Exercise library with muscle targets, sets &amp; reps</li>
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Indian diet charts: High-protein, Vegetarian, Weight Loss</li>
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Personal Training (PT) sessions &amp; commission tracking</li>
                            </ul>
                        </div>
                        <div class="lg:col-span-7 bg-[#141518] rounded-lg p-5 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800 text-xs">
                                <span class="font-bold text-white">Trainer: Rahul Verma</span>
                                <span class="text-amber-400 font-medium">10 Assigned Members</span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-white">Assigned: Chest &amp; Triceps Routine</span>
                                    <span class="text-emerald-400 text-[11px] font-medium">Active This Week</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400">
                                    <div class="p-2 rounded-lg bg-slate-950 border border-slate-800 flex justify-between">
                                        <span>Bench Press</span>
                                        <span class="text-amber-400 font-bold">4 × 10 reps</span>
                                    </div>
                                    <div class="p-2 rounded-lg bg-slate-950 border border-slate-800 flex justify-between">
                                        <span>Dumbbell Incline</span>
                                        <span class="text-amber-400 font-bold">3 × 12 reps</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-semibold text-white">Diet Chart: 2,400 kcal &bull; 140g Protein</div>
                                    <div class="text-[10px] text-slate-400">Veg + Whey Plan with Meal Timings</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md bg-emerald-500/20 text-emerald-400 font-bold text-[10px]">ACTIVE</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- APP 3: MEMBER WEB PASS --}}
                <div x-show="activeApp === 'member'" x-cloak class="rounded-xl bg-[#1B1D21] p-4 sm:p-6 border border-slate-800">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                        <div class="lg:col-span-5 space-y-4">
                            <span class="px-3 py-1 rounded-md bg-blue-500/10 text-blue-400 text-xs font-bold uppercase tracking-normal">Member Web Pass</span>
                            <h3 class="text-2xl sm:text-3xl font-semibold text-white">Instant QR Check-in Without App Downloads</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Members don’t need to install a heavy app from Play Store. They simply open their link to show their digital gym pass, scan the gym QR, and track their attendance streak.
                            </p>
                            <ul class="space-y-2.5 text-xs sm:text-sm text-slate-300 pt-1">
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Digital gym pass with remaining days counter</li>
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Attendance streak 🔥 keeps members motivated to show up</li>
                                <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Direct 1-tap UPI fee renewal via PhonePe or Google Pay</li>
                            </ul>
                        </div>
                        <div class="lg:col-span-7 bg-[#141518] rounded-lg p-5 border border-slate-800 space-y-3">
                            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-amber-400 font-bold uppercase tracking-normal">Active Digital Pass</div>
                                    <div class="text-lg font-bold text-white">Ankush Singh</div>
                                    <div class="text-[11px] text-slate-400">12-Month Membership &bull; Valid till 02 Dec 2026</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-semibold text-emerald-400">30</div>
                                    <div class="text-[10px] text-slate-400 uppercase font-bold">Days Left</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-3">
                                    <div class="text-2xl">🔥</div>
                                    <div>
                                        <div class="text-xs font-bold text-white">18-Day Streak</div>
                                        <div class="text-[10px] text-emerald-400 font-medium">Consistent Member</div>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-3">
                                    <div class="text-2xl">🥗</div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Daily Diet Target</div>
                                        <div class="text-[10px] text-slate-400">High-Protein 2,400 kcal</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         BIOMETRIC INTEGRATION (NO BRAND NAMES - GENERIC HARDWARE)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-16 bg-[#141518] border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-normal text-amber-400">Biometric Integration</span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-white tracking-tight mt-1">
                    Connect Any Access Device or Check-in Mode
                </h2>
                <p class="text-slate-400 text-sm sm:text-base mt-2">
                    Connect your existing hardware in minutes without paying for expensive static IPs or local servers.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="p-5 rounded-lg bg-[#1B1D21] border border-slate-800 text-center hover:border-slate-700 transition-colors">
                    <div class="text-2xl mb-2">👤</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Face Recognition</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">High-Speed Contactless</div>
                </div>
                <div class="p-5 rounded-lg bg-[#1B1D21] border border-slate-800 text-center hover:border-slate-700 transition-colors">
                    <div class="text-2xl mb-2">👆</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Fingerprint Scanners</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Optical &amp; Capacitive</div>
                </div>
                <div class="p-5 rounded-lg bg-[#1B1D21] border border-slate-800 text-center hover:border-slate-700 transition-colors">
                    <div class="text-2xl mb-2">🛑</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Turnstiles &amp; Gates</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Auto-Lock Expired Entry</div>
                </div>
                <div class="p-5 rounded-lg bg-[#1B1D21] border border-slate-800 text-center hover:border-slate-700 transition-colors">
                    <div class="text-2xl mb-2">📲</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Dynamic QR Scan</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Member Phone Check-in</div>
                </div>
                <div class="p-5 rounded-lg bg-[#1B1D21] border border-slate-800 text-center hover:border-slate-700 transition-colors">
                    <div class="text-2xl mb-2">💳</div>
                    <div class="font-bold text-white text-xs sm:text-sm">RFID Smart Cards</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Tap-and-Go Keyfobs</div>
                </div>
                <div class="p-5 rounded-lg bg-[#1B1D21] border border-slate-800 text-center hover:border-slate-700 transition-colors">
                    <div class="text-2xl mb-2">🖥️</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Front-Desk Entry</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Fast Name/Phone Search</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         AUTOMATED WHATSAPP FEE RECOVERY (CLEAN NATURAL COLORS & COPY)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-20 lg:py-24 bg-[#0D0E10]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                {{-- Left Text Info --}}
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-emerald-400 text-xs font-bold uppercase tracking-normal">
                        <span>💬</span> WhatsApp Fee Recovery
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-white tracking-tight leading-tight">
                        Collect Pending Gym Fees On Time, Without Awkward Follow-up Calls
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Chasing members for renewals at the front desk is uncomfortable for everyone. GymDeskHub sends friendly reminders on WhatsApp with direct UPI payment links so members can settle fees in seconds.
                    </p>

                    <div class="space-y-4 pt-1">
                        <div class="flex items-start gap-3.5">
                            <div class="w-7 h-7 rounded-lg bg-slate-800 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</div>
                            <div>
                                <h4 class="text-white font-bold text-sm">Polite Reminder 3 Days Before</h4>
                                <p class="text-slate-400 text-xs mt-0.5">Members get a friendly heads-up with their renewal amount before their membership ends.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <div class="w-7 h-7 rounded-lg bg-slate-800 text-amber-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</div>
                            <div>
                                <h4 class="text-white font-bold text-sm">Direct UPI Pay Link (GPay, PhonePe, Paytm)</h4>
                                <p class="text-slate-400 text-xs mt-0.5">Members tap once to pay. The amount goes directly to your gym's bank account with zero extra commission.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <div class="w-7 h-7 rounded-lg bg-slate-800 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</div>
                            <div>
                                <h4 class="text-white font-bold text-sm">Automatic GST Invoice on Payment</h4>
                                <p class="text-slate-400 text-xs mt-0.5">A clear PDF receipt is sent to the member on WhatsApp immediately after payment is confirmed.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right WhatsApp Chat Bubble Mockup (Polished Realistic Colors) --}}
                <div class="lg:col-span-6 flex justify-center">
                    <div class="w-full max-w-sm rounded-xl bg-[#0b141a] border border-slate-800 p-4 space-y-3 font-sans ">
                        {{-- WhatsApp Header --}}
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                            <div class="w-10 h-10 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-white text-sm">
                                AG
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                    <span class="truncate">Armour 24/7 Gym</span>
                                    <span class="text-emerald-400 text-xs shrink-0">✓</span>
                                </div>
                                <div class="text-[10px] text-slate-400">Official Business Account</div>
                            </div>
                        </div>

                        {{-- Chat Message Bubble --}}
                        <div class="p-4 rounded-lg bg-[#1f2c34] text-slate-100 text-xs space-y-3">
                            <p class="font-normal text-slate-200 leading-relaxed">
                                Hi <strong>Rohit Sharma</strong>! 👋<br>
                                Your Annual Membership at <strong>Armour 24/7 Gym</strong> expires in <strong>3 days</strong> (12 Dec 2026).
                            </p>
                            <p class="text-slate-300 text-[11px] leading-relaxed">
                                Tap below to renew easily and keep your <strong>18-Day Workout Streak 🔥</strong> going strong!
                            </p>
                            <div class="p-3 rounded-xl bg-[#111b21] border border-slate-700/60 flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-normal">Renewal Amount</div>
                                    <div class="text-sm font-bold text-amber-400">₹6,000 / year</div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-medium text-[10px] border border-emerald-500/20">Zero Surcharge</span>
                            </div>
                            <div class="pt-1">
                                <div class="w-full py-2.5 rounded-xl bg-[#00a884] hover:bg-[#029071] text-white font-semibold text-center text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>⚡</span> Tap to Pay via UPI / PhonePe
                                </div>
                            </div>
                            <div class="flex justify-end items-center gap-1 text-[10px] text-slate-400 pt-0.5">
                                <span>10:32 AM</span>
                                <span class="text-cyan-400">✓✓</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         MODERN HIGH-END COMPARISON MATRIX (REDESIGNED PROFESSIONAL UI)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-20 bg-[#141518] border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="text-xs font-bold uppercase tracking-normal text-amber-400">Why Upgrade?</span>
                <h2 class="text-3xl sm:text-4xl font-semibold text-white tracking-tight mt-2">
                    Paper Registers vs GymDeskHub
                </h2>
                <p class="text-slate-400 text-sm sm:text-base mt-2">
                    Compare everyday administration workflows in one connected platform.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                
                {{-- Left: Old Manual Way --}}
                <div class="p-6 sm:p-8 rounded-xl bg-[#1B1D21] border border-rose-500/20 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-rose-400">The Old Manual Way</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Paper Registers, Excel &amp; Memory</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 text-xs font-bold">Old School</span>
                        </div>

                        <ul class="space-y-4 text-xs sm:text-sm text-slate-300">
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✕</span>
                                <span><strong>Unchecked Gate Entry:</strong> Members continue working out weeks after their package has expired.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✕</span>
                                <span><strong>Awkward Phone Calls:</strong> Front desk staff spend hours every evening calling people for pending dues.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✕</span>
                                <span><strong>Lost Diet &amp; Workout Sheets:</strong> Hand-written paper routines get misplaced within 2 days.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✕</span>
                                <span><strong>Cash vs UPI Mismatch:</strong> Daily counter collection rarely matches the register at closing time.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✕</span>
                                <span><strong>Zero Multi-Branch Sync:</strong> Visiting another branch requires manual verification calls.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Right: GymDeskHub Way --}}
                <div class="p-6 sm:p-8 rounded-xl bg-[#1B1D21] border-2 border-amber-500 flex flex-col justify-between relative shadow-sm">
                    <div class="absolute -top-3.5 right-6 px-3.5 py-1 rounded-full bg-amber-500 text-slate-950 text-[11px] font-semibold uppercase tracking-normal">
                        Modern Gym OS
                    </div>
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-white">The GymDeskHub Way</h3>
                                <p class="text-xs text-amber-400 mt-0.5">Connected Hardware, WhatsApp &amp; Mobile Web</p>
                            </div>
                        </div>

                        <ul class="space-y-4 text-xs sm:text-sm text-slate-200">
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                                <span><strong>Smart Biometric Integration:</strong> Turnstile gates lock automatically the minute a plan expires. Zero tailgating.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                                <span><strong>Automated WhatsApp Recovery:</strong> Members receive friendly renewal alerts with 1-tap UPI links. 70% pay early.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                                <span><strong>Trainer App In Pocket:</strong> Exercise libraries, sets &amp; reps, and custom Indian diet charts sent right to member phones.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                                <span><strong>Fast POS Counter:</strong> Split cash + online payments in a single transaction with automatic GST receipt generation.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                                <span><strong>Central Multi-Branch Dashboard:</strong> Check revenue, staff actions, and attendance from anywhere on your phone.</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         PRICING PREVIEW (MATTE DARK - STRICTLY ANNUAL ONLY)
    ══════════════════════════════════════════════════════════════ --}}
    <section id="plans" class="marketing-section py-20 lg:py-24 bg-[#0D0E10] border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-normal text-amber-400">Clear &bull; Transparent &bull; Affordable</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-white tracking-tight mt-2 mb-4">
                    Simple Annual Pricing Built For Gyms
                </h2>
                <p class="text-slate-400 text-base sm:text-lg mb-8">
                    Choose the plan suited to your facility. Zero monthly recurring friction, zero per-member penalties.
                </p>

                {{-- Annual Pill (Matte) --}}
                <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-200 text-xs sm:text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Annual Billing &bull; Free Assisted Database Migration &bull; Zero Monthly Surcharges</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch max-w-6xl mx-auto">

                {{-- FREE FOREVER --}}
                <div class="p-8 rounded-xl bg-[#1B1D21] border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-normal">Free Forever</span>
                        </div>
                        <div class="text-4xl font-semibold text-white">₹0</div>
                        <div class="text-xs text-slate-400 mt-1 font-medium">Free forever &bull; Up to 75 members</div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            Ideal for boutique studios, independent yoga/crossfit coaches, and gyms starting out.
                        </p>
                        <ul class="space-y-3 text-xs text-slate-300 my-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Up to 75 active member profiles</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Membership packages &amp; tax invoicing</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Manual attendance register &amp; QR check-in</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Member self-service portal</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'free_forever']) }}"
                       class="w-full py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm text-center transition-colors">
                        Get Started Free
                    </a>
                </div>

                {{-- STARTER (HIGHLIGHTED) --}}
                <div class="p-8 rounded-xl bg-[#1B1D21] border-2 border-amber-500 flex flex-col justify-between relative">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-amber-500 text-slate-950 text-[11px] font-semibold uppercase tracking-normal">
                        Most Popular
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 rounded-md bg-amber-500/10 text-amber-400 text-xs font-bold uppercase tracking-normal">Starter</span>
                            <span class="text-xs text-emerald-400 font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">Annual Plan</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-semibold text-white">₹6,000</span>
                            <span class="text-xs text-slate-400">/year</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">Single branch &bull; <strong>Unlimited Members</strong></div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            Complete automation for busy single-location gyms: biometric integration, attendance, and automated WhatsApp fee recovery.
                        </p>
                        <div class="text-xs font-semibold text-white my-3">Everything in Free, plus:</div>
                        <ul class="space-y-3 text-xs text-slate-300 mb-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Unlimited member records</strong></li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Biometric integration</strong> (Face &amp; Fingerprint access)</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Automated WhatsApp recovery</strong> + UPI pay links</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> GST billing, POS counter &amp; split payments</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'starter']) }}"
                       class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-sm text-center transition-colors">
                        Choose Starter Plan
                    </a>
                </div>

                {{-- PRO CHAIN --}}
                <div class="p-8 rounded-xl bg-[#1B1D21] border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-normal">Pro Chain</span>
                            <span class="text-xs text-emerald-400 font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">Annual Plan</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-semibold text-white">₹12,000</span>
                            <span class="text-xs text-slate-400">/year</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">Multi-branch chains &bull; <strong>Central Command</strong></div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            For scaling gym chains requiring central owner analytics, roaming memberships, and inventory management.
                        </p>
                        <div class="text-xs font-semibold text-white my-3">Everything in Starter, plus:</div>
                        <ul class="space-y-3 text-xs text-slate-300 mb-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Multi-branch centralized dashboards</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Supplement store POS with barcode inventory</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Personal trainer payroll &amp; commission engine</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Dedicated technical onboarding engineer</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'pro']) }}"
                       class="w-full py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm text-center transition-colors">
                        Choose Pro Chain
                    </a>
                </div>

            </div>

            {{-- Guarantee Strip --}}
            <div class="mt-12 text-center text-xs text-slate-400 flex flex-wrap items-center justify-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>7-Day Full Money-Back Guarantee</span>
                </span>
                <span>•</span>
                <span>Fast Setup Within 24 Hours</span>
                <span>•</span>
                <span>Secure Payments via UPI, Cards &amp; NetBanking</span>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         PROFESSIONAL REAL GYM FEEDBACK SHOWCASE
    ══════════════════════════════════════════════════════════════ --}}
    

    {{-- ══════════════════════════════════════════════════════════════
         PROFESSIONALLY ANIMATED FAQ ACCORDION (SPLIT LAYOUT - LARGE & POLISHED)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-20 lg:py-28 bg-[#0D0E10] border-t border-slate-800" x-data="{ open: 0 }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                
                {{-- Left Side: Direct Reassurance & WhatsApp CTA --}}
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-28">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-amber-400 text-xs font-bold uppercase tracking-normal">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Frequently Asked Questions</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-white tracking-tight leading-[1.15]">
                        Everything You Need to Know Before Starting
                    </h2>
                    <p class="text-slate-300 text-base sm:text-lg leading-relaxed">
                        Have questions about biometric integration, automated WhatsApp fee recovery, or moving member data? Here are straightforward answers.
                    </p>

                    <div class="p-6 sm:p-7 rounded-xl bg-[#1B1D21] border border-slate-800 space-y-4 mt-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-lg border border-amber-500/20">
                                💬
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Have a unique gym setup?</h3>
                                <p class="text-xs text-slate-400">Our technical team is ready to help</p>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Need help selecting hardware or configuring multi-branch accounts? Speak directly with our team.
                        </p>
                        <div class="pt-2 flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('contact') }}" class="w-full text-center px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs sm:text-sm font-semibold transition-colors">
                                Contact Team
                            </a>
                            @php
                                $supportPhone = $platformSettings['support_phone'] ?? '+91 9974995179';
                                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$supportPhone);
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Hi GymDeskHub, I have a question about gym software') }}" target="_blank"
                               class="w-full text-center px-4 py-3 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 text-xs sm:text-sm font-semibold transition-colors flex items-center justify-center gap-1.5">
                                <span>Chat on WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Large, Spacious, Premium Accordions --}}
                <div class="lg:col-span-7 space-y-4 sm:space-y-5">
                    @foreach([
                        [
                            'num' => '01',
                            'q' => 'Can I connect my existing biometric machine?',
                            'a' => 'Yes, absolutely. GymDeskHub works with standard face recognition terminals, fingerprint scanners, RFID smart cards, and automated access gates. You do not need to replace your existing machines or purchase costly static IP addresses.'
                        ],
                        [
                            'num' => '02',
                            'q' => 'How does the automated WhatsApp fee reminder work?',
                            'a' => 'The software automatically tracks member validity. It sends polite WhatsApp reminders 3 days before expiry, on the due date, and when overdue — complete with an instant UPI payment link (GPay, PhonePe, Paytm). Payments go directly into your gym\'s bank account with zero extra commission.'
                        ],
                        [
                            'num' => '03',
                            'q' => 'Can I import my existing member database from Excel?',
                            'a' => 'Yes! You can upload your existing Excel or CSV file in seconds. Our technical onboarding team also provides free assisted data migration so all your member profiles, remaining package validity, and past balances transfer smoothly without interruption.'
                        ],
                        [
                            'num' => '04',
                            'q' => 'Is the Free Forever plan really free with no time limit?',
                            'a' => 'Yes! The Free Forever tier has zero trial expiration and requires no credit card. You can manage up to 75 active gym members completely free with full access to member records, manual attendance, and tax billing. Upgrade to our ₹6,000/year Starter plan only when your gym crosses 75 members or requires biometric gate integration.'
                        ],
                        [
                            'num' => '05',
                            'q' => 'Do my gym members have to download a heavy mobile app?',
                            'a' => 'No. GymDeskHub operates as a lightweight Progressive Web App (PWA). Members simply open their private gym link in any mobile browser to view their digital membership card, check attendance streak records, and access daily workout routines without consuming phone storage.'
                        ],
                    ] as $i => $faq)
                        <div class="rounded-lg sm:rounded-xl transition-all duration-200 overflow-hidden"
                             :class="open === {{ $i }} ? 'bg-[#151618] border-2 border-amber-500/70 shadow-lg' : 'bg-[#1B1D21] border border-slate-800 hover:border-slate-700'">
                            
                            <button type="button" 
                                    class="w-full flex items-center justify-between gap-4 sm:gap-6 px-6 sm:px-8 py-5 sm:py-6 text-left transition-colors cursor-pointer group"
                                    @click="open = open === {{ $i }} ? null : {{ $i }}">
                                
                                <div class="flex items-center gap-3.5 sm:gap-5 min-w-0">
                                    <span class="font-mono text-xs sm:text-sm font-bold shrink-0 transition-colors"
                                          :class="open === {{ $i }} ? 'text-amber-400' : 'text-slate-500 group-hover:text-slate-400'">
                                        {{ $faq['num'] }}
                                    </span>
                                    <h3 class="text-base sm:text-lg lg:text-xl font-bold text-white leading-snug tracking-tight">
                                        {{ $faq['q'] }}
                                    </h3>
                                </div>

                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-lg border flex items-center justify-center shrink-0 transition-all duration-300"
                                     :class="open === {{ $i }} ? 'bg-amber-500 text-slate-950 border-amber-500 rotate-180' : 'bg-slate-900 border-slate-800 text-slate-400 group-hover:text-white group-hover:border-slate-700'">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <div x-show="open === {{ $i }}" 
                                 x-collapse 
                                 x-cloak 
                                 class="px-6 sm:px-8 pb-6 sm:pb-7 pt-1 text-slate-300 text-sm sm:text-base leading-relaxed border-t border-slate-800/80">
                                <div class="pl-7 sm:pl-10">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FINAL CLOSING CTA (CLEAN MATTE)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-20 bg-[#1B1D21] border-t border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-white tracking-tight mb-4">
                Ready to Upgrade Your Gym to Autopilot?
            </h2>
            <p class="text-slate-400 text-base sm:text-lg mb-8 max-w-2xl mx-auto">
                Join fitness club owners across India running attendance, billing, and member workouts with complete peace of mind.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-base transition-colors">
                    Start Free Forever &rarr;
                </a>
                <a href="{{ route('contact') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 font-semibold text-base hover:bg-slate-800 hover:text-white transition-colors">
                    Book a Free Demo
                </a>
            </div>
        </div>
    </section>

</x-guest-layout>
