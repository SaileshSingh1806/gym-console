<x-guest-layout title="Pricing Plans — {{ ($platformSettings['app_name'] ?? 'Gym Console') }}">
    <div class="py-12 sm:py-16 lg:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-slate-300 text-xs sm:text-sm font-medium mb-6">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Simple, Predictable Pricing</span>
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-white tracking-tight mb-4">
                Invest in growth, not software overhead
            </h1>
            <p class="text-slate-400 text-base sm:text-lg">
                No per-member charges. No hidden payment gateway fees. Pay annually and scale without restrictions.
            </p>
        </div>

        {{-- WhatsApp Banner --}}
        <div class="text-center mb-12">
            <a href="https://wa.me/919876543210?text=Hi,%20I%20am%20interested%20in%20custom%20pricing%20for%20Gym%20Console" target="_blank"
               class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-emerald-400 hover:text-emerald-300 transition-colors bg-emerald-500/10 border border-emerald-500/20 px-4 py-2 rounded-full">
                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                <span>Need custom hardware deployment or 5+ branches? Chat with our team on WhatsApp</span>
            </a>
        </div>

        {{-- 3 Pricing Cards Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto mb-20">

            {{-- 1. FREE FOREVER --}}
            <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-wider">Free Forever</span>
                    </div>
                    <div class="text-4xl font-bold text-white">₹0</div>
                    <div class="text-xs text-slate-400 mt-1">Free forever &bull; Up to 75 members</div>
                    <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                        For independent trainers and boutique studios starting out.
                    </p>

                    <div class="my-4 text-xs font-semibold text-slate-300">Included features:</div>
                    <ul class="space-y-3 text-xs text-slate-300 mb-8">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Complete member profiles & photos</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Manual attendance tracking</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Membership packages & offers</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Payment recording & PDF invoices</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Body measurement & progress logs</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> CSV data export anytime</li>
                    </ul>
                </div>

                <div>
                    <a href="{{ route('register', ['plan' => 'free_forever']) }}"
                       class="w-full py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm text-center block transition-colors">
                        Create Free Account
                    </a>
                    <span class="text-[11px] text-slate-500 text-center block mt-2">No credit card required</span>
                </div>
            </div>

            {{-- 2. STARTER (FEATURED) --}}
            <div class="p-8 rounded-2xl bg-slate-900 border-2 border-amber-500 flex flex-col justify-between relative shadow-xl shadow-amber-500/10 lg:-translate-y-2">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-amber-500 text-slate-950 text-[11px] font-bold uppercase tracking-wider">
                    Most Popular
                </div>
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-400 text-xs font-bold uppercase tracking-wider">Starter</span>
                        <span class="text-xs text-amber-400 font-semibold">33% OFF</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-bold text-white">₹6,000</span>
                        <span class="text-xs text-slate-400 line-through">₹9,000</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-1">per year + 18% GST &bull; 1 branch, unlimited members</div>
                    <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                        Complete automation with biometric turnstiles, WhatsApp reminders, and mobile app.
                    </p>

                    <div class="my-4 text-xs font-semibold text-white">Everything in Free, plus:</div>
                    <ul class="space-y-3 text-xs text-slate-300 mb-8">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Unlimited members</strong> — zero caps</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Face recognition, thumb &amp; RFID gate sync</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Automated WhatsApp reminders + UPI payment links</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Mobile Member App (Android &amp; iOS)</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 600+ animated workout video portal</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> GST billing, tax reports &amp; staff role accounts</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Free assisted data migration</li>
                    </ul>
                </div>

                <div>
                    <a href="{{ route('register', ['plan' => 'starter']) }}"
                       class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm text-center block transition-colors shadow-lg shadow-amber-500/20">
                        Buy Starter Plan
                    </a>
                    <span class="text-[11px] text-slate-400 text-center block mt-2">₹500/month equivalent &bull; Billed annually</span>
                </div>
            </div>

            {{-- 3. PRO (CHAIN) --}}
            <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-wider">Pro Chain</span>
                        <span class="text-xs text-amber-400 font-semibold">33% OFF</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-bold text-white">₹12,000</span>
                        <span class="text-xs text-slate-400 line-through">₹18,000</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-1">per year + 18% GST &bull; Multi-branch chains</div>
                    <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                        For multi-location gym chains needing a white-label mobile app and centralized reporting.
                    </p>

                    <div class="my-4 text-xs font-semibold text-slate-300">Everything in Starter, plus:</div>
                    <ul class="space-y-3 text-xs text-slate-300 mb-8">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Branded mobile app</strong> under your gym name</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Multi-branch consolidation reports</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Thumb &amp; Face Recognition door lock sync</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Supplement shop POS &amp; inventory tracking</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Class scheduling &amp; batch management</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Dedicated WhatsApp account manager</li>
                    </ul>
                </div>

                <div>
                    <a href="{{ route('register', ['plan' => 'pro']) }}"
                       class="w-full py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm text-center block transition-colors">
                        Buy Pro Plan
                    </a>
                    <span class="text-[11px] text-slate-500 text-center block mt-2">₹1,000/month equivalent &bull; Billed annually</span>
                </div>
            </div>

        </div>

        {{-- FAQ Section --}}
        <div class="max-w-3xl mx-auto pt-12 border-t border-slate-800">
            <h2 class="text-2xl sm:text-3xl font-bold text-white text-center mb-8">Frequently Asked Questions</h2>
            <div class="space-y-4" x-data="{ open: null }">
                @foreach([
                    ['q' => 'Can I upgrade or downgrade plans later?', 'a' => 'Yes. You can upgrade from Free Forever to Starter or Pro at any time. When upgrading, your existing members, packages, and historical logs are preserved without disruption.'],
                    ['q' => 'Do I need any special hardware for biometric access?', 'a' => 'Gym Console works with standard eSSL, Hikvision Face MinMoe, Realtime, ZKTeco, and Mantra machines. If you already have existing biometric devices, our desktop sync tool connects to them directly.'],
                    ['q' => 'Are there any hidden transaction fees on WhatsApp or payments?', 'a' => 'None from Gym Console. When members pay via your dynamic UPI links, 100% of the funds settle directly into your gym\'s bank account.'],
                ] as $i => $faq)
                    <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
                        <button class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left font-semibold text-white hover:text-amber-400 transition-colors focus:outline-none"
                                @click="open = open === {{ $i }} ? null : {{ $i }}">
                            <span class="text-sm sm:text-base">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 text-slate-400 transition-transform duration-200"
                                 :class="open === {{ $i }} ? 'rotate-180 text-amber-400' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak class="px-6 pb-4">
                            <p class="text-slate-400 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-guest-layout>
