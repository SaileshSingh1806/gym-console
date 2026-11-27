<x-guest-layout 
    title="Contact GymDeskHub — Schedule a Free Live Demo & Hardware Consultation"
    meta-description="Book a personalized live demo of GymDeskHub or get expert support for biometric access control, WhatsApp automation, and member data migration."
    meta-keywords="contact gymdeskhub, book gym software demo, biometric hardware setup gym, gym management software support"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (MATTE DARK - POPPINS)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero relative pt-12 pb-16 sm:pt-20 sm:pb-24 lg:pt-24 lg:pb-28 bg-[#0D0E10] border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            
            {{-- Category Pill Badge (Matches Home Page Style & Responsive) --}}

            <h1 class="text-4xl sm:text-6xl font-semibold text-white tracking-tight leading-[1.14] max-w-4xl mx-auto mb-6">
                Let's talk about your gym.
            </h1>

            <p class="text-base sm:text-lg text-slate-300 max-w-3xl mx-auto leading-relaxed font-normal">
                Whether you need help selecting biometric access devices, migrating thousands of existing members from Excel, or getting a 1-on-1 walkthrough — our team is ready to assist you.
            </p>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         CONTACT INFO & INQUIRY FORM SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-16 sm:py-20 lg:py-24 bg-[#141518]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">

                {{-- Left: Contact Channels (5 Cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div>
                        <span class="text-xs uppercase font-bold tracking-normal text-amber-400">Get In Touch</span>
                        <h2 class="mt-1 text-2xl sm:text-3xl font-semibold text-white">We're Available 7 Days a Week</h2>
                        <p class="mt-2 text-xs sm:text-sm text-slate-400 leading-relaxed">
                            No automated runarounds. Speak directly with a software specialist who understands gym operations and hardware setup.
                        </p>
                    </div>

                    @php
                        $contactPhone = $platformSettings['support_phone'] ?? '+91 99749 95179';
                        $cleanContactPhone = preg_replace('/[^0-9]/', '', (string)$contactPhone);
                        if (empty($cleanContactPhone)) {
                            $cleanContactPhone = '919974995179';
                        } elseif (strlen($cleanContactPhone) === 10) {
                            $cleanContactPhone = '91' . $cleanContactPhone;
                        }
                    @endphp

                    {{-- Channel 1: WhatsApp (Fastest) --}}
                    <a href="https://wa.me/{{ $cleanContactPhone }}?text={{ urlencode('Hi GymDeskHub, I want to discuss gym software for my fitness center') }}" 
                       target="_blank"
                       class="p-5 sm:p-6 rounded-xl bg-[#1B1D21] border border-slate-800 hover:border-emerald-500/50 transition-colors flex items-start gap-4 block group">
                        <div class="w-12 h-12 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20 text-xl">
                            💬
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-normal px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20">Fastest Response</span>
                                <span class="text-[11px] text-slate-500 font-mono">Instant</span>
                            </div>
                            <div class="text-base font-bold text-white mt-1 group-hover:text-emerald-300 transition-colors">WhatsApp Direct Chat</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $contactPhone }} (Active Daily 9 AM – 9 PM IST)</div>
                        </div>
                    </a>

                    {{-- Channel 2: Phone Calling --}}
                    <a href="tel:{{ $contactPhone }}"
                       class="p-5 sm:p-6 rounded-xl bg-[#1B1D21] border border-slate-800 hover:border-amber-500/50 transition-colors flex items-start gap-4 block group">
                        <div class="w-12 h-12 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20 text-xl">
                            📞
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold text-amber-400 uppercase tracking-normal px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 inline-block">Direct Call</div>
                            <div class="text-base font-bold text-white mt-1 group-hover:text-amber-300 transition-colors">Call Support Team</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $contactPhone }} &bull; Mon–Sat 10 AM – 7 PM</div>
                        </div>
                    </a>

                    {{-- Channel 3: Email Inquiries --}}
                    <div class="p-5 sm:p-6 rounded-xl bg-[#1B1D21] border border-slate-800 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/20 text-xl">
                            ✉️
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold text-blue-400 uppercase tracking-normal px-2 py-0.5 rounded bg-blue-500/10 border border-blue-500/20 inline-block">Official Email</div>
                            <div class="text-base font-bold text-white mt-1"><a href="mailto:sales@gymdeskhub.in">sales@gymdeskhub.in</a></div>
                            <div class="text-xs text-slate-400 mt-0.5">Technical: support@gymdeskhub.in</div>
                        </div>
                    </div>

                    {{-- Channel 4: Biometric Consultation --}}
                    <div class="p-5 sm:p-6 rounded-xl bg-[#1B1D21] border border-slate-800 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0 border border-purple-500/20 text-xl">
                            🔒
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold text-purple-400 uppercase tracking-normal px-2 py-0.5 rounded bg-purple-500/10 border border-purple-500/20 inline-block">Hardware Team</div>
                            <div class="text-base font-bold text-white mt-1">Biometric Integration Assistance</div>
                            <div class="text-xs text-slate-400 mt-0.5">Face terminals, fingerprint scanners &amp; turnstile gates</div>
                        </div>
                    </div>

                    {{-- Channel 5: Office Location & Legal Entity --}}
                    <div class="p-5 sm:p-6 rounded-xl bg-[#1B1D21] border border-slate-800 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center shrink-0 border border-slate-700 text-xl">
                            📍
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-normal px-2 py-0.5 rounded bg-slate-800 border border-slate-700 inline-block">Headquarters &amp; Legal Entity</div>
                            <div class="text-base font-bold text-white mt-1">GymDeskHub</div>
                            <div class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                Individual Enterprise &bull; Proprietor: <strong class="text-white font-medium">Sailesh Singh</strong><br>
                                Ahmedabad, Gujarat 380015, India<br>
                                Grievance: <a href="mailto:grievance@gymdeskhub.in" class="text-amber-400 hover:underline">grievance@gymdeskhub.in</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Inquiry & Demo Form (7 Cols) --}}
                <div class="lg:col-span-7">
                    <div class="p-7 sm:p-9 rounded-xl bg-[#1B1D21] border border-slate-800 " x-data="{ submitted: false, inquiryUrl: '' }">
                        <div x-show="!submitted">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                                <div>
                                    <h3 class="text-xl sm:text-2xl font-bold text-white">Book a Demo or Ask a Question</h3>
                                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Fill out the details below. Our team responds within 2 business hours.</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs font-bold border border-amber-500/20 hidden sm:inline-block">Free Consultation</span>
                            </div>

                            <form @submit.prevent="const data = new FormData($event.target); const message = ['Hello GymDeskHub, I would like to discuss your software.', ...Array.from(data.entries()).map(([key,value]) => key + ': ' + value)].join(String.fromCharCode(10)); inquiryUrl = 'https://wa.me/{{ $cleanContactPhone }}?text=' + encodeURIComponent(message); submitted = true" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-normal mb-2">Your Full Name *</label>
                                        <input type="text" name="Name" maxlength="150" required placeholder="e.g. Vikram Rathore"
                                               class="w-full px-4 py-3 rounded-xl bg-[#0D0E10] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-sm transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-normal mb-2">Gym / Fitness Center Name *</label>
                                        <input type="text" name="Gym" maxlength="150" required placeholder="e.g. Apex Fitness Studio"
                                               class="w-full px-4 py-3 rounded-xl bg-[#0D0E10] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-sm transition-colors">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-normal mb-2">WhatsApp / Phone Number *</label>
                                        <input type="tel" name="Phone" maxlength="150" required placeholder="+91 98765 43210"
                                               class="w-full px-4 py-3 rounded-xl bg-[#0D0E10] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-sm transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-normal mb-2">Email Address *</label>
                                        <input type="email" name="Email" maxlength="150" required placeholder="vikram@apexfitness.in"
                                               class="w-full px-4 py-3 rounded-xl bg-[#0D0E10] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-sm transition-colors">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-normal mb-2">Number of Gym Branches</label>
                                        <select name="Branches" class="w-full px-4 py-3 rounded-xl bg-[#0D0E10] border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm transition-colors cursor-pointer">
                                            <option value="1">1 Branch (Standalone Gym)</option>
                                            <option value="2-3">2 – 3 Branches</option>
                                            <option value="4-10">4 – 10 Branches</option>
                                            <option value="10+">10+ Branches (Enterprise Chain)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-normal mb-2">How Can We Help You?</label>
                                        <select name="Topic" class="w-full px-4 py-3 rounded-xl bg-[#0D0E10] border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm transition-colors cursor-pointer">
                                            <option value="demo">Book a 1-on-1 Live Demo</option>
                                            <option value="biometric">Biometric &amp; Turnstile Integration</option>
                                            <option value="migration">Migrate Data from Excel or Old Software</option>
                                            <option value="pricing">Custom Annual Pricing for Multi-Branch</option>
                                            <option value="general">General Support Question</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-normal mb-2">Your Requirements / Message (Optional)</label>
                                    <textarea name="Message" maxlength="1000" rows="4" placeholder="Tell us about your current member count, hardware in use, or any specific questions..."
                                              class="w-full px-4 py-3 rounded-xl bg-[#0D0E10] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-sm transition-colors resize-none"></textarea>
                                </div>

                                <button type="submit"
                                        class="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm sm:text-base transition-colors cursor-pointer ">
                                    Continue to WhatsApp &rarr;
                                </button>
                            </form>

                            <div class="mt-6 pt-5 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
                                <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Free Assisted Data Migration</span>
                                <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Zero Credit Card Required</span>
                                <span class="flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Direct support</span>
                            </div>
                        </div>

                        {{-- Success Feedback State --}}
                        <div x-show="submitted" x-cloak class="py-16 text-center space-y-4">
                            <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto text-2xl font-bold border border-emerald-500/20">
                                ✓
                            </div>
                            <h3 class="text-2xl font-bold text-white">Your inquiry is ready</h3>
                            <p class="text-sm text-slate-300 max-w-md mx-auto leading-relaxed">
                                Open WhatsApp to review and send your inquiry to our support team. Nothing has been submitted yet.
                            </p>
                            <div class="pt-4">
                                <a :href="inquiryUrl" target="_blank"
                                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm transition-colors">
                                    <span>Open WhatsApp &amp; send &rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         QUICK QUESTIONS STRIP
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section py-16 sm:py-20 bg-[#0D0E10] border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-normal text-amber-400">Need Quick Answers?</span>
                <h3 class="text-2xl sm:text-3xl font-semibold text-white tracking-tight mt-1">Common Questions From Gym Owners</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <div class="p-6 rounded-xl bg-[#1B1D21] border border-slate-800 space-y-2">
                    <h4 class="text-base font-bold text-white">How quickly can my gym go live?</h4>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        In less than 24 hours. Once you share your member Excel list, our team migrates all active records so you can start recording check-ins and payments immediately.
                    </p>
                </div>
                <div class="p-6 rounded-xl bg-[#1B1D21] border border-slate-800 space-y-2">
                    <h4 class="text-base font-bold text-white">Do you assist with hardware wiring?</h4>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Yes. Our team provides step-by-step guidance for connecting your face recognition machines, fingerprint scanners, and turnstile relays over your local gym network.
                    </p>
                </div>
                <div class="p-6 rounded-xl bg-[#1B1D21] border border-slate-800 space-y-2">
                    <h4 class="text-base font-bold text-white">Can I schedule a weekend demo?</h4>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Yes! Our team works 7 days a week. Pick any convenient morning or evening time slot that fits your gym's non-peak hours.
                    </p>
                </div>
            </div>
        </div>
    </section>

</x-guest-layout>
