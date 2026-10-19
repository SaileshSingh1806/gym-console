<x-guest-layout title="Contact Us — {{ ($platformSettings['app_name'] ?? 'Gym Console') }}">
    {{-- Hero Section --}}
    <section class="relative pt-12 pb-16 sm:pt-20 sm:pb-20 lg:pt-24 lg:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-slate-300 text-xs sm:text-sm font-medium mb-6">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Direct Support & Consultation</span>
            </div>

            <h1 class="text-4xl sm:text-5xl font-bold text-white tracking-tight leading-tight max-w-4xl mx-auto mb-4">
                We're here to help your gym grow
            </h1>

            <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Have questions about pricing, multi-branch scaling, or biometric machine integration (eSSL, Hikvision, Realtime, ZKTeco)? Our specialist team is ready to assist you.
            </p>
        </div>
    </section>

    {{-- Contact Info & Form Section --}}
    <section class="py-6 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

                {{-- Left: Contact Channels (5 Cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div>
                        <span class="text-xs uppercase font-bold tracking-wider text-amber-400">Get In Touch</span>
                        <h2 class="mt-1 text-2xl font-extrabold text-white">Talk to Our Team</h2>
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">
                            Whether you're opening your first gym branch or upgrading a chain with 20+ locations, our team is here to assist.
                        </p>
                    </div>

                    {{-- Channel 1: WhatsApp --}}
                    @php
                        $contactPhone = $platformSettings['support_phone'] ?? '+91 99749 95179';
                        $cleanContactPhone = preg_replace('/[^0-9]/', '', (string)$contactPhone);
                        if (empty($cleanContactPhone)) {
                            $cleanContactPhone = '919974995179';
                        } elseif (strlen($cleanContactPhone) === 10) {
                            $cleanContactPhone = '91' . $cleanContactPhone;
                        }
                    @endphp
                    <a href="https://wa.me/{{ $cleanContactPhone }}?text={{ urlencode('Hi, I am interested in Gym Console') }}" target="_blank"
                       class="p-5 rounded-2xl bg-[#0c121e]/90 border border-slate-800/80 hover:border-emerald-500/40 hover:bg-[#0e1627] transition-all flex items-start gap-4 block group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Fastest Response</div>
                            <div class="text-sm font-bold text-white mt-0.5 group-hover:text-emerald-300 transition-colors">WhatsApp Direct Support</div>
                            <div class="text-xs text-slate-400 mt-0.5">+91 99749 95179 (Mon–Sat, 9AM–8PM IST)</div>
                        </div>
                    </a>

                    {{-- Channel 2: Email --}}
                    <div class="p-5 rounded-2xl bg-[#0c121e]/90 border border-slate-800/80 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Email Inquiry</div>
                            <div class="text-sm font-bold text-white mt-0.5">sales@gymconsole.in</div>
                            <div class="text-xs text-slate-400 mt-0.5">support@gymconsole.in for technical tickets</div>
                        </div>
                    </div>

                    {{-- Channel 3: Hardware Consultation --}}
                    <div class="p-5 rounded-2xl bg-[#0c121e]/90 border border-slate-800/80 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-blue-400 uppercase tracking-wider">Hardware Setup Consultation</div>
                            <div class="text-sm font-bold text-white mt-0.5">Hikvision, eSSL &amp; ZKTeco</div>
                            <div class="text-xs text-slate-400 mt-0.5">Network, cloud sync, and turnstile gate wiring assistance</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Inquiry Form (7 Cols) --}}
                <div class="lg:col-span-7">
                    <div class="p-6 sm:p-8 rounded-3xl bg-[#0c121e]/90 border border-slate-800/80 shadow-2xl" x-data="{ submitted: false }">
                        <div x-show="!submitted">
                            <h3 class="text-xl font-bold text-white mb-1">Send an Inquiry</h3>
                            <p class="text-xs text-slate-400 mb-6">Fill in your details and our team will get back to you within 2 business hours.</p>

                            <form @submit.prevent="submitted = true" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Your Name *</label>
                                        <input type="text" required placeholder="e.g. Amit Verma"
                                               class="w-full px-4 py-2.5 rounded-xl bg-[#080d17] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none text-sm transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Gym / Business Name *</label>
                                        <input type="text" required placeholder="e.g. Iron Core Fitness"
                                               class="w-full px-4 py-2.5 rounded-xl bg-[#080d17] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none text-sm transition-colors">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Business Email *</label>
                                        <input type="email" required placeholder="amit@gym.com"
                                               class="w-full px-4 py-2.5 rounded-xl bg-[#080d17] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none text-sm transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone / WhatsApp Number *</label>
                                        <input type="tel" required placeholder="+91 98765 43210"
                                               class="w-full px-4 py-2.5 rounded-xl bg-[#080d17] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none text-sm transition-colors">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Number of Branches</label>
                                        <select class="w-full px-4 py-2.5 rounded-xl bg-[#080d17] border border-slate-800 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none text-sm transition-colors cursor-pointer">
                                            <option value="1">1 Branch (Single Location)</option>
                                            <option value="2-3">2 - 3 Branches</option>
                                            <option value="4-10">4 - 10 Branches</option>
                                            <option value="10+">10+ Branches (Enterprise Chain)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Inquiry Type</label>
                                        <select class="w-full px-4 py-2.5 rounded-xl bg-[#080d17] border border-slate-800 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none text-sm transition-colors cursor-pointer">
                                            <option value="pricing">Custom Pricing & Plans</option>
                                            <option value="biometrics">Biometric & Turnstile Integration</option>
                                            <option value="demo">Live Platform Demo</option>
                                            <option value="migration">Data Migration from Old Software</option>
                                            <option value="other">General Support</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Message / Requirements</label>
                                    <textarea rows="4" placeholder="Tell us about your gym setup, current member count, or specific requirements..."
                                              class="w-full px-4 py-2.5 rounded-xl bg-[#080d17] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 focus:outline-none text-sm transition-colors resize-none"></textarea>
                                </div>

                                <button type="submit"
                                        class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm transition-all cursor-pointer shadow-lg shadow-amber-500/20">
                                    Send Inquiry
                                </button>
                            </form>
                        </div>

                        {{-- Success State --}}
                        <div x-show="submitted" x-cloak class="py-12 text-center space-y-4">
                            <div class="w-14 h-14 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto text-xl font-bold border border-emerald-500/20">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white">Thank You!</h3>
                            <p class="text-xs text-slate-400 max-w-md mx-auto">
                                Your inquiry has been received. One of our specialists will reach out via WhatsApp and Email within 2 business hours.
                            </p>
                            <div class="pt-2">
                                <a href="https://wa.me/{{ $cleanContactPhone }}?text={{ urlencode('Hi, I just submitted an inquiry on Gym Console.') }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 text-slate-950 font-bold text-xs hover:bg-emerald-400 transition-colors">
                                    Chat Now on WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</x-guest-layout>
