<x-guest-layout 
    title="Gym Software Pricing Plans — Simple & Transparent | GymDeskHub"
    meta-description="Transparent gym management software pricing with zero per-member charges. Annual plans with unlimited members, biometric access control sync, WhatsApp automation & 24/7 support."
    meta-keywords="gym software pricing, gym management software cost, affordable gym software india, gym erp subscription, gym software price"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (MATTE DARK - POPPINS)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero relative pt-12 pb-16 sm:pt-20 sm:pb-24 lg:pt-24 lg:pb-28 bg-[#0D0E10] border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            
            {{-- Category Pill Badge (Matches Home Page Style & Responsive) --}}

            <h1 class="text-4xl sm:text-6xl font-semibold text-white tracking-tight leading-[1.14] max-w-4xl mx-auto mb-6">
                Simple plans for every stage of your gym.
            </h1>

            <p class="text-base sm:text-lg text-slate-300 max-w-3xl mx-auto leading-relaxed mb-8 font-normal">
                No percentage cuts on your payments, no hidden fees for adding members, and no unexpected charges. One straightforward annual subscription that pays for itself in recovered dues.
            </p>

            {{-- Annual Billing Guarantee Strip (Matte) --}}
            <div class="inline-flex flex-wrap items-center justify-center gap-3 px-5 py-2.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 text-xs sm:text-sm font-medium">
                <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Annual Billing Only</span>
                </span>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span>Free Assisted Database Migration</span>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span>Zero Commission on UPI Payments</span>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         3 PRICING TIERS GRID
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-16 sm:py-20 lg:py-24 bg-[#141518]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto mb-20">

                {{-- 1. FREE FOREVER --}}
                <div class="p-8 sm:p-9 rounded-xl bg-[#1B1D21] border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <span class="px-3.5 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-normal">Free Forever</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-semibold text-white">₹0</span>
                            <span class="text-xs text-slate-400 font-medium">forever</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">Up to 75 active members &bull; Single facility</div>
                        
                        <p class="text-xs sm:text-sm text-slate-400 mt-4 pb-6 border-b border-slate-800 leading-relaxed">
                            Perfect for boutique fitness studios, independent yoga/crossfit coaches, and personal trainers getting started.
                        </p>

                        <div class="my-5 text-xs font-bold uppercase tracking-normal text-slate-300">Included Capabilities:</div>
                        <ul class="space-y-3.5 text-xs sm:text-sm text-slate-300 mb-8">
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Up to 75 active member profiles
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Manual attendance register &amp; QR check-in
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Flexible membership packages &amp; offers
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Fee collection logging &amp; printable PDF bills
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Body measurement &amp; transformation logs
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> CSV &amp; Excel data export anytime
                            </li>
                        </ul>
                    </div>

                    <div>
                        <a href="{{ route('register', ['plan' => 'free_forever']) }}"
                           class="w-full py-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm text-center block transition-colors">
                            Create Free Account
                        </a>
                        <span class="text-[11px] text-slate-500 text-center block mt-2.5">No credit card or payment required</span>
                    </div>
                </div>

                {{-- 2. STARTER (FEATURED - MATTE GOLD BORDER) --}}
                <div class="p-8 sm:p-9 rounded-xl bg-[#1B1D21] border-2 border-amber-500 flex flex-col justify-between relative  ">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-amber-500 text-slate-950 text-[11px] font-semibold uppercase tracking-normal">
                        Most Popular Choice
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <span class="px-3.5 py-1 rounded-md bg-amber-500/10 text-amber-400 text-xs font-bold uppercase tracking-normal border border-amber-500/20">Starter</span>
                            <span class="text-xs text-emerald-400 font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">Annual Plan</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-semibold text-white">₹6,000</span>
                            <span class="text-xs text-slate-400">/year + GST</span>
                        </div>
                        <div class="text-xs text-amber-400 mt-1 font-semibold">Equivalent to just ₹500 / month &bull; Unlimited Members</div>
                        
                        <p class="text-xs sm:text-sm text-slate-300 mt-4 pb-6 border-b border-slate-800 leading-relaxed">
                            The complete operating system for standalone gyms. Automates gate turnstiles, WhatsApp renewals, and attendance.
                        </p>

                        <div class="my-5 text-xs font-bold uppercase tracking-normal text-amber-400">Everything in Free, plus:</div>
                        <ul class="space-y-3.5 text-xs sm:text-sm text-slate-200 mb-8">
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-400 font-bold">✓</span> <strong class="text-white">Unlimited Members:</strong> Zero caps or per-member charges
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-400 font-bold">✓</span> <strong class="text-white">Biometric Integration:</strong> Face recognition, thumb &amp; RFID gate sync
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-400 font-bold">✓</span> <strong class="text-white">WhatsApp Automation:</strong> Auto-reminders with direct UPI payment links
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-400 font-bold">✓</span> Progressive Web App (PWA) Member Pass
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-400 font-bold">✓</span> 600+ animated workout exercise database
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-400 font-bold">✓</span> GST invoicing, tax reports &amp; staff permissions
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-400 font-bold">✓</span> Free assisted member data migration by our team
                            </li>
                        </ul>
                    </div>

                    <div>
                        <a href="{{ route('register', ['plan' => 'starter']) }}"
                           class="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm text-center block transition-colors">
                            Upgrade to Starter &rarr;
                        </a>
                        <span class="text-[11px] text-slate-400 text-center block mt-2.5">Instant activation &bull; 7-Day Money-Back Guarantee</span>
                    </div>
                </div>

                {{-- 3. PRO (CHAIN) --}}
                <div class="p-8 sm:p-9 rounded-xl bg-[#1B1D21] border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <span class="px-3.5 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-normal">Pro Multi-Branch</span>
                            <span class="text-xs text-amber-400 font-semibold px-2 py-0.5 rounded-full bg-amber-500/10">Multi-Location</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-semibold text-white">₹12,000</span>
                            <span class="text-xs text-slate-400">/year + GST</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">Equivalent to ₹1,000 / month &bull; Multi-branch chains</div>
                        
                        <p class="text-xs sm:text-sm text-slate-400 mt-4 pb-6 border-b border-slate-800 leading-relaxed">
                            Built for growing gym chains and premium fitness centers that need centralized management and roaming access.
                        </p>

                        <div class="my-5 text-xs font-bold uppercase tracking-normal text-slate-300">Everything in Starter, plus:</div>
                        <ul class="space-y-3.5 text-xs sm:text-sm text-slate-300 mb-8">
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> <strong class="text-white">Multi-Branch Command:</strong> Central view for all your gym locations
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Cross-branch member roaming with proper branch revenue crediting
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Supplement shop POS counter &amp; inventory tracking
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Personal trainer commission &amp; client session ledger
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Group fitness class booking &amp; batch capacity caps
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span> Priority WhatsApp technical support manager
                            </li>
                        </ul>
                    </div>

                    <div>
                        <a href="{{ route('register', ['plan' => 'pro']) }}"
                           class="w-full py-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm text-center block transition-colors">
                            Choose Pro Chain
                        </a>
                        <span class="text-[11px] text-slate-500 text-center block mt-2.5">Custom hardware setups supported</span>
                    </div>
                </div>

            </div>

            {{-- Custom Requirements Help Banner --}}
            @php
                $pricingPhone = $platformSettings['support_phone'] ?? '+91 9974995179';
                $cleanPricingPhone = preg_replace('/[^0-9]/', '', (string)$pricingPhone);
                if (empty($cleanPricingPhone)) {
                    $cleanPricingPhone = '919974995179';
                } elseif (strlen($cleanPricingPhone) === 10) {
                    $cleanPricingPhone = '91' . $cleanPricingPhone;
                }
            @endphp
            <div class="max-w-4xl mx-auto p-6 sm:p-8 rounded-xl bg-[#1B1D21] border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6 mb-24">
                <div class="space-y-1 text-center sm:text-left">
                    <div class="text-sm font-bold text-white flex items-center justify-center sm:justify-start gap-2">
                        <span>💬</span> Need custom biometric gates or managing 5+ locations?
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Our technical deployment specialists are available to plan custom turnstile installations and hardware setups across India.
                    </p>
                </div>
                <a href="https://wa.me/{{ $cleanPricingPhone }}?text={{ urlencode('Hi GymDeskHub, I need custom pricing for multi-branch gym software') }}" 
                   target="_blank"
                   class="shrink-0 px-6 py-3 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 font-semibold text-xs sm:text-sm transition-colors flex items-center gap-2">
                    <span>Chat on WhatsApp &rarr;</span>
                </a>
            </div>

            {{-- ══════════════════════════════════════════════════════════════
                 COMPREHENSIVE PLAN COMPARISON MATRIX
            ══════════════════════════════════════════════════════════════ --}}
            <div class="max-w-6xl mx-auto mb-24">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-normal text-amber-400">Compare Plans</span>
                    <h2 class="text-3xl sm:text-4xl font-semibold text-white tracking-tight mt-2">
                        Detailed Feature Comparison
                    </h2>
                </div>

                <p class="text-center text-xs text-slate-400 mb-3 md:hidden flex items-center justify-center gap-1.5">
                    <span>👉 Swipe horizontally to view all plans</span>
                </p>
                <div class="overflow-x-auto rounded-xl border border-slate-800 bg-[#1B1D21]">
                    <table class="w-full min-w-[620px] text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 bg-[#0D0E10]">
                                <th class="p-4 sm:p-5 text-white font-bold w-2/5">Capability</th>
                                <th class="p-4 sm:p-5 text-slate-300 font-bold text-center w-1/5">Free Forever</th>
                                <th class="p-4 sm:p-5 text-amber-400 font-bold text-center w-1/5 bg-amber-500/5 border-x border-slate-800">Starter</th>
                                <th class="p-4 sm:p-5 text-slate-300 font-bold text-center w-1/5">Pro Chain</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-300">
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Active Member Limit</td>
                                <td class="p-4 sm:p-5 text-center text-slate-400">Up to 75</td>
                                <td class="p-4 sm:p-5 text-center font-bold text-amber-400 bg-amber-500/5 border-x border-slate-800">Unlimited</td>
                                <td class="p-4 sm:p-5 text-center font-bold text-white">Unlimited</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Number of Gym Branches</td>
                                <td class="p-4 sm:p-5 text-center text-slate-400">1 Branch</td>
                                <td class="p-4 sm:p-5 text-center text-slate-300 bg-amber-500/5 border-x border-slate-800">1 Branch</td>
                                <td class="p-4 sm:p-5 text-center font-bold text-white">Multi-Branch</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Biometric Gate Integration (Face &amp; Thumb)</td>
                                <td class="p-4 sm:p-5 text-center text-slate-600">—</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold bg-amber-500/5 border-x border-slate-800">✓ Included</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold">✓ Included</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Automated WhatsApp Fee Reminders (with UPI Links)</td>
                                <td class="p-4 sm:p-5 text-center text-slate-600">—</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold bg-amber-500/5 border-x border-slate-800">✓ Included</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold">✓ Included</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Point of Sale (POS) &amp; Split Cash/UPI Counter</td>
                                <td class="p-4 sm:p-5 text-center text-slate-400">Basic Manual</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold bg-amber-500/5 border-x border-slate-800">✓ Full POS</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold">✓ Full POS + Stock</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">GST-Compliant Tax Invoices (PDF on WhatsApp)</td>
                                <td class="p-4 sm:p-5 text-center text-slate-400">Standard Bill</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold bg-amber-500/5 border-x border-slate-800">✓ Automated</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold">✓ Automated</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Trainer Management &amp; Commission Ledger</td>
                                <td class="p-4 sm:p-5 text-center text-slate-600">—</td>
                                <td class="p-4 sm:p-5 text-center text-slate-300 bg-amber-500/5 border-x border-slate-800">Standard</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold">✓ Advanced PT Tracking</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Cross-Branch Member Roaming</td>
                                <td class="p-4 sm:p-5 text-center text-slate-600">—</td>
                                <td class="p-4 sm:p-5 text-center text-slate-600 bg-amber-500/5 border-x border-slate-800">—</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold">✓ Included</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Free Assisted Member Data Migration</td>
                                <td class="p-4 sm:p-5 text-center text-slate-400">Self-Service CSV</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold bg-amber-500/5 border-x border-slate-800">✓ Done by Our Team</td>
                                <td class="p-4 sm:p-5 text-center text-emerald-400 font-bold">✓ Done by Our Team</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-semibold text-white">Support Channels</td>
                                <td class="p-4 sm:p-5 text-center text-slate-400">Community / Help Center</td>
                                <td class="p-4 sm:p-5 text-center text-slate-200 bg-amber-500/5 border-x border-slate-800">WhatsApp &amp; Phone</td>
                                <td class="p-4 sm:p-5 text-center font-bold text-amber-400">Dedicated Priority Manager</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════
                 EXPANDED FAQ SECTION (LARGE & POLISHED)
            ══════════════════════════════════════════════════════════════ --}}
            <div class="max-w-4xl mx-auto pt-6" x-data="{ open: 0 }">
                <div class="text-center mb-12">
                    <span class="text-xs font-bold uppercase tracking-normal text-amber-400">Answers to Common Questions</span>
                    <h2 class="text-3xl sm:text-4xl font-semibold text-white tracking-tight mt-2">
                        Frequently Asked Questions About Pricing
                    </h2>
                </div>

                <div class="space-y-4">
                    @foreach([
                        [
                            'num' => '01',
                            'q' => 'Do I need to buy special biometric hardware to start using GymDeskHub?',
                            'a' => 'No hardware is required to get started! You can use GymDeskHub on any smartphone, tablet, or laptop immediately to manage members, admission records, and fee billing. When you are ready to add automated turnstile gates or facial recognition attendance, GymDeskHub seamlessly connects with standard biometric machines with zero hardware replacement.'
                        ],
                        [
                            'num' => '02',
                            'q' => 'Can I upgrade from Free Forever to Starter later as my gym grows?',
                            'a' => 'Yes, absolutely. You can use the Free Forever tier for as long as you like. When your active member base crosses 75 or you decide to install biometric attendance, you can upgrade to Starter with 1 click. All your existing members, packages, payment history, and records will be preserved seamlessly.'
                        ],
                        [
                            'num' => '03',
                            'q' => 'Does GymDeskHub take any cut or commission on member UPI payments?',
                            'a' => 'Zero commission. Unlike other platforms that route money through third-party wallets and deduct 2% to 3%, GymDeskHub integrates direct UPI payment links. When members pay via Google Pay, PhonePe, or Paytm, 100% of the funds go straight into your gym\'s bank account.'
                        ],
                        [
                            'num' => '04',
                            'q' => 'How does the free member data migration process work?',
                            'a' => 'If you have an existing Excel sheet, register register, or are moving from another software, simply share your file with us. Our technical team cleans the data, matches member packages, remaining days, and contact numbers, and uploads everything into your new account within 2 hours.'
                        ],
                        [
                            'num' => '05',
                            'q' => 'What happens if I need help setting up the software?',
                            'a' => 'Our team provides full onboarding support over WhatsApp, Google Meet, or phone call. We will guide you through setting up packages, staff logins, and connecting your attendance devices so your gym is fully operational on day one.'
                        ],
                    ] as $i => $faq)
                        <div class="rounded-lg sm:rounded-xl transition-all duration-200 overflow-hidden"
                             :class="open === {{ $i }} ? 'bg-[#1B1D21] border-2 border-amber-500/70 shadow-lg' : 'bg-[#1B1D21] border border-slate-800 hover:border-slate-700'">
                            
                            <button type="button" 
                                    class="w-full flex items-center justify-between gap-4 sm:gap-6 px-6 sm:px-8 py-5 sm:py-6 text-left transition-colors cursor-pointer group"
                                    @click="open = open === {{ $i }} ? null : {{ $i }}">
                                
                                <div class="flex items-center gap-3.5 sm:gap-5 min-w-0">
                                    <span class="font-mono text-xs sm:text-sm font-bold shrink-0 transition-colors"
                                          :class="open === {{ $i }} ? 'text-amber-400' : 'text-slate-500 group-hover:text-slate-400'">
                                        {{ $faq['num'] }}
                                    </span>
                                    <h3 class="text-base sm:text-lg font-bold text-white leading-snug tracking-tight">
                                        {{ $faq['q'] }}
                                    </h3>
                                </div>

                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl border flex items-center justify-center shrink-0 transition-all duration-300"
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
         CLOSING CTA BANNER
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-20 lg:py-24 bg-[#0D0E10] border-t border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-white tracking-tight mb-4">
                Start Saving Time and Stopping Revenue Leaks
            </h2>
            <p class="text-slate-300 text-base sm:text-lg mb-8 max-w-2xl mx-auto">
                Join forward-thinking gym owners across India running smooth attendance, WhatsApp billing, and automated gate security.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-base transition-colors">
                    Start Free Forever &rarr;
                </a>
                <a href="{{ route('contact') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 font-semibold text-base hover:bg-slate-800 hover:text-white transition-colors">
                    Book a Free 1-on-1 Demo
                </a>
            </div>
        </div>
    </section>

</x-guest-layout>
