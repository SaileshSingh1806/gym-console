<x-guest-layout title="Gym Console - Modern Multi-Tenant Gym Management SaaS">
    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-16 pb-24 lg:pt-24 lg:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold mb-8">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Multi-Tenant SaaS • Hikvision Biometrics • Flutter Mobile App
                </div>
                <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Scale Your Gym Empire with <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-400 to-amber-200">Ultimate Precision</span>
                </h1>
                <p class="mt-6 text-lg text-slate-400 leading-relaxed">
                    The all-in-one multi-branch fitness management platform. Automated memberships, real-time Hikvision turnstile biometric access control, workout & diet builders, financial POS, and mobile apps.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold hover:brightness-110 shadow-xl shadow-orange-500/20 transition-all text-center">
                        Start 14-Day Free Trial
                    </a>
                    <a href="#plans" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 font-semibold hover:bg-slate-800 transition-all text-center">
                        View SaaS Pricing
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Feature Highlights Grid -->
    <section class="py-20 bg-slate-900/50 border-y border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs uppercase font-bold tracking-widest text-amber-400">Engineered for Fitness Businesses</h2>
                <p class="mt-2 text-3xl font-bold text-white">Everything needed to run 1 or 50 gym branches</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="p-8 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Multi-Tenant & Multi-Branch</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Each gym business has strictly isolated data, role-based staff permissions, and unified management across multiple facility branches.</p>
                </div>

                <!-- Card 2 -->
                <div class="p-8 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11a7.96 7.96 0 001.328 4.417m11.536-1.077a9 9 0 01-1.364 3.79"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Hikvision & IoT Biometrics</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Automatic turnstile gate unlocking, facial recognition scanners, RFID check-in, and instant anti-passback access denial for expired members.</p>
                </div>

                <!-- Card 3 -->
                <div class="p-8 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Flutter Mobile API (/api/v1)</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">High-performance Laravel REST API with Sanctum tokens. Members can generate dynamic turnstile QR codes, check routines, and log workouts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Grid Matching Design -->
    <section id="plans" class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Top WhatsApp Link -->
            <div class="text-center mb-10">
                <a href="https://wa.me/919876543210?text=Hi,%20I%20am%20interested%20in%20custom%20pricing%20for%20Gym%20Console" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-400 hover:text-emerald-300 transition-colors bg-emerald-500/10 border border-emerald-500/20 px-5 py-2 rounded-full">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                    </svg>
                    <span>Contact us directly on WhatsApp for custom pricing & instant plan setup</span>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto">
                <!-- CARD 1: FREE FOREVER -->
                <div class="rounded-3xl bg-white text-slate-800 p-8 border border-slate-200 shadow-xl flex flex-col justify-between relative">
                    <div>
                        <div class="text-center">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-slate-950 text-white text-[11px] font-bold uppercase tracking-wider">
                                <span>🎁</span> FREE FOREVER
                            </span>
                        </div>
                        <div class="text-center mt-5 mb-2">
                            <h3 class="text-xs font-black tracking-widest text-slate-500 uppercase">FREE FOREVER</h3>
                            <div class="text-5xl font-black text-slate-950 mt-1">₹0</div>
                            <div class="text-xs font-medium text-slate-500 mt-1">/ year — no trial, no card, no expiry</div>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed max-w-xs mx-auto">
                                Free forever, not a 14-day trial. Upgrade only when you outgrow it.
                            </p>
                        </div>
                        <div class="my-5 text-center">
                            <span class="inline-block px-4 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                For gyms with up to 75 members
                            </span>
                        </div>
                        <ul class="space-y-2.5 text-xs text-slate-700 my-6">
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Member database & profiles</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Manual attendance</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Membership packages, offers & services</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Payment recording & PDF invoices</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Body measurements & progress photos</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Dashboard & member reports</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Email notifications</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Export your data anytime</span></li>
                            <li class="flex items-start gap-2.5 text-slate-400 line-through"><span>—</span><span>WhatsApp reminders</span></li>
                            <li class="flex items-start gap-2.5 text-slate-400 line-through"><span>—</span><span>Face, thumb & RFID attendance</span></li>
                            <li class="flex items-start gap-2.5 text-slate-400 line-through"><span>—</span><span>Fitplex member app</span></li>
                            <li class="flex items-start gap-2.5 text-slate-400 line-through"><span>—</span><span>GST reports</span></li>
                        </ul>
                    </div>
                    <div class="pt-4 text-center">
                        <a href="{{ route('register', ['plan' => 'free_forever']) }}" class="w-full py-3.5 rounded-full bg-slate-950 hover:bg-slate-800 text-white font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-md">
                            <span>Create free account</span><span>→</span>
                        </a>
                        <span class="text-[11px] text-slate-400 mt-2 block font-medium">Live in 2 minutes. No sales call.</span>
                    </div>
                </div>

                <!-- CARD 2: STARTER (ORANGE HIGHLIGHTED) -->
                <div class="rounded-3xl bg-[#ea580c] text-white p-8 shadow-2xl shadow-orange-600/30 flex flex-col justify-between relative transform lg:-translate-y-2 border-2 border-orange-400">
                    <div>
                        <div class="text-center">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-slate-950 text-white text-[11px] font-bold uppercase tracking-wider shadow-md">
                                <span>🔥</span> MOST POPULAR
                            </span>
                        </div>
                        <div class="text-center mt-5 mb-2">
                            <h3 class="text-xs font-black tracking-widest text-white/90 uppercase">STARTER</h3>
                            <div class="flex items-center justify-center gap-2 mt-1">
                                <span class="line-through text-white/75 text-sm font-semibold">₹9,000</span>
                                <span class="px-2 py-0.5 rounded-md bg-slate-950 text-white font-black text-[10px]">33% OFF</span>
                            </div>
                            <div class="text-5xl font-black text-white mt-1">₹6,000</div>
                            <div class="text-xs font-medium text-white/90 mt-1">/ year + 18% GST</div>
                            <p class="text-xs text-white/90 mt-2 leading-relaxed max-w-xs mx-auto">
                                Launch offer — you save ₹3,000. Works out at ₹500/month, billed once a year.
                            </p>
                        </div>
                        <div class="my-5 text-center">
                            <span class="inline-block px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold">
                                For a single gym, unlimited members
                            </span>
                        </div>
                        <div class="text-xs font-bold text-white mb-3">
                            Everything in Free, plus:
                        </div>
                        <ul class="space-y-2.5 text-xs text-white/95 my-4">
                            <li class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>Unlimited members — no per-member fee</span></li>
                            <li class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>Face Recognition, Thumb & RFID attendance</span></li>
                            <li class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>Automated WhatsApp reminders with UPI link</span></li>
                            <li class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>GST billing, invoices & GST reports</span></li>
                            <li class="space-y-1.5">
                                <div class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>Fitplex Member App (Android & iOS)</span></div>
                                <div class="flex items-center gap-2 pl-6">
                                    <span class="px-2.5 py-1 rounded-md bg-white/20 text-[10px] font-bold flex items-center gap-1 border border-white/30">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M3 20.5v-17c0-.83.94-1.3 1.6-.8l14 8.5c.67.41.67 1.39 0 1.8l-14 8.5c-.66.5-1.6.03-1.6-.8z"/></svg> Google Play
                                    </span>
                                    <span class="px-2.5 py-1 rounded-md bg-white/20 text-[10px] font-bold flex items-center gap-1 border border-white/30">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.85c.64-.78 1.08-1.86.96-2.95-1 .04-2.16.67-2.82 1.45-.58.67-1.1 1.76-.96 2.82 1.11.09 2.18-.54 2.82-1.32z"/></svg> App Store
                                    </span>
                                </div>
                            </li>
                            <li class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>600+ animated workout video portal</span></li>
                            <li class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>Staff & trainer accounts with roles</span></li>
                            <li class="flex items-start gap-2.5"><span class="font-bold">✓</span><span>Free data migration from your old software</span></li>
                            <li class="flex items-start gap-2.5 text-white/50 line-through"><span>—</span><span>Multi-branch consolidation</span></li>
                            <li class="flex items-start gap-2.5 text-white/50 line-through"><span>—</span><span>Door lock access control</span></li>
                        </ul>
                    </div>
                    <div class="pt-4 text-center">
                        <a href="{{ route('register', ['plan' => 'starter']) }}" class="w-full py-3.5 rounded-full bg-slate-950 hover:bg-black text-white font-bold text-sm transition-all flex items-center justify-center shadow-lg">
                            <span>Buy Starter Plan</span>
                        </a>
                    </div>
                </div>

                <!-- CARD 3: PRO -->
                <div class="rounded-3xl bg-white text-slate-800 p-8 border border-slate-200 shadow-xl flex flex-col justify-between relative">
                    <div>
                        <div class="text-center mt-2 mb-2">
                            <h3 class="text-xs font-black tracking-widest text-slate-500 uppercase">PRO</h3>
                            <div class="flex items-center justify-center gap-2 mt-1">
                                <span class="line-through text-slate-400 text-sm font-semibold">₹18,000</span>
                                <span class="px-2 py-0.5 rounded-md bg-[#ea580c] text-white font-black text-[10px]">33% OFF</span>
                            </div>
                            <div class="text-5xl font-black text-slate-950 mt-1">₹12,000</div>
                            <div class="text-xs font-medium text-slate-500 mt-1">/ year + 18% GST</div>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed max-w-xs mx-auto">
                                Launch offer — you save ₹6,000. Works out at ₹1,000/month, billed once a year.
                            </p>
                        </div>
                        <div class="my-5 text-center">
                            <span class="inline-block px-4 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                For gym chains & multi-branch operators
                            </span>
                        </div>
                        <div class="text-xs font-bold text-slate-900 mb-3">
                            Everything in Starter, plus:
                        </div>
                        <ul class="space-y-2.5 text-xs text-slate-700 my-4">
                            <li class="space-y-1.5">
                                <div class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Your own Branded Mobile App — published under your gym's name</span></div>
                                <div class="flex items-center gap-2 pl-6">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center gap-1 border border-slate-200">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M3 20.5v-17c0-.83.94-1.3 1.6-.8l14 8.5c.67.41.67 1.39 0 1.8l-14 8.5c-.66.5-1.6.03-1.6-.8z"/></svg> Google Play
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center gap-1 border border-slate-200">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.85c.64-.78 1.08-1.86.96-2.95-1 .04-2.16.67-2.82 1.45-.58.67-1.1 1.76-.96 2.82 1.11.09 2.18-.54 2.82-1.32z"/></svg> App Store
                                    </span>
                                </div>
                            </li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Multi-branch consolidation reports</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Thumb & Face Recognition door lock control</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Supplement Shop POS & inventory</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Class scheduling & batch management</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>API & door gate sync integrations</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Priority onboarding & setup</span></li>
                            <li class="flex items-start gap-2.5"><span class="text-slate-800 font-bold">✓</span><span>Dedicated WhatsApp account manager</span></li>
                        </ul>
                    </div>
                    <div class="pt-4 text-center">
                        <a href="{{ route('register', ['plan' => 'pro']) }}" class="w-full py-3.5 rounded-full bg-slate-950 hover:bg-slate-800 text-white font-bold text-sm transition-all flex items-center justify-center shadow-md">
                            <span>Buy Pro Plan</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-guest-layout>

