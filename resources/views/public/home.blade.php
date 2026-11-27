<x-guest-layout 
    title="GymDeskHub™ — All-in-One Gym Management Software & Biometric Integration"
    meta-description="Manage your gym members, attendance, fee collections, and trainer workouts from one simple platform. Plug & play biometric integration and automated WhatsApp fee reminders."
    meta-keywords="gym management software, best gym software india, gym membership software, biometric integration, gym attendance system, trainer app, member app, gym billing pos, whatsapp gym reminders"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (CLEAN MATTE DARK - NO GLOW - HUMAN TONE)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero bg-[#0D0E10] border-b border-slate-800">
    <div class="mx-auto grid lg:grid-cols-12 gap-10 items-center">
        <div class="lg:col-span-6">
            <p class="text-sm text-amber-400 font-semibold mb-5">Gym management software</p>
            <h1 class="text-white mb-6">Run your gym without the chaos.</h1>
            <p class="text-lg text-slate-300 max-w-xl mb-8">Manage members, attendance, payments and coaching in one workspace, from the front desk to your phone.</p>
            <div class="flex flex-wrap gap-3 items-center">
                <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-amber-500 text-slate-950 font-semibold rounded-lg">Start free &rarr;</a>
                <a href="#front-desk" class="inline-flex items-center px-5 py-3 border border-slate-700 rounded-lg text-white font-medium">See how it works</a>
            </div>
            <p class="mt-4 text-sm text-slate-400">No card needed for the free plan.</p>
        </div>
        <div class="lg:col-span-6 relative py-8">
            <div class="rounded-[20px] border border-slate-700 bg-[#141518] overflow-hidden">
                <div class="flex items-center gap-2 p-4 border-b border-slate-800"><span class="w-2 h-2 rounded-full bg-slate-600"></span><span class="w-2 h-2 rounded-full bg-slate-600"></span><span class="w-2 h-2 rounded-full bg-slate-600"></span><span class="ml-2 text-xs text-slate-400">GymDeskHub / Owner workspace</span></div>
                <div class="p-6 sm:p-8">
                    @if(file_exists(public_path('images/marketing/dashboard.webp')))
                        <img src="{{ asset('images/marketing/dashboard.webp') }}" width="1440" height="900" fetchpriority="high" alt="GymDeskHub owner dashboard" class="w-full h-auto rounded-lg">
                    @else
                        <p class="text-xs text-slate-400 mb-5">Product workflow overview</p>
                        <div class="space-y-3">
                            @foreach([['Members','Memberships and renewals'],['Attendance','Daily check-in records'],['Payments','Collections and outstanding fees'],['Trainers','Assignments and coaching']] as [$label,$description])
                                <div class="flex justify-between gap-3 border-b border-slate-800 pb-3"><span class="text-white font-medium">{{ $label }}</span><span class="text-xs text-slate-400 text-right">{{ $description }}</span></div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2 justify-end mt-3">
                @foreach([['QR check-in','.12'],['Renewal reminders','.25'],['Daily collections','.08']] as [$label,$speed])
                    <div data-parallax data-speed="{{ $speed }}" class="px-3 py-2 rounded-lg border border-slate-700 bg-[#1B1D21] text-xs text-slate-300">{{ $label }}</div>
                @endforeach
            </div>
        </div>
    </div>
</section>
<section id="front-desk" class="marketing-section bg-[#141518] border-b border-slate-800">
    <div class="mx-auto grid lg:grid-cols-12 gap-10 lg:gap-16 items-start">
        <div class="lg:col-span-5 lg:sticky lg:top-28">
            <p class="text-sm text-amber-400 mb-3">Daily operations</p><h2 class="text-white mb-5">A day at your front desk.</h2>
            <p class="text-slate-400">Follow the everyday work of a gym, from the first check-in to the last payment.</p>
        </div>
        <div class="lg:col-span-7 divide-y divide-slate-800">
            @foreach([['01','Member arrives','Find the member and record attendance using the available check-in workflow.','checkin'],['02','Collect a fee','Record the payment and review any remaining balance.','members'],['03','Follow up on renewals','Find expiring memberships and prepare a WhatsApp reminder.','whatsapp'],['04','Review the day','Open the owner dashboard to review collections and attendance.','dashboard']] as [$number,$heading,$description,$image])
                <article data-reveal class="py-8 first:pt-0">
                    <div class="text-sm text-amber-400 mb-2">{{ $number }}</div><h3 class="text-white mb-3">{{ $heading }}</h3><p class="text-slate-300">{{ $description }}</p>
                    @if(file_exists(public_path('images/marketing/'.$image.'.webp')))
                        <div class="mt-5 rounded-[20px] border border-slate-700 overflow-hidden"><img loading="lazy" decoding="async" src="{{ asset('images/marketing/'.$image.'.webp') }}" width="1440" height="900" alt="{{ $heading }}" class="w-full h-auto"></div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
<section class="marketing-section bg-[#0D0E10]">
    <div class="mx-auto">
        <h2 class="text-white mb-8">Tools for every part of your gym.</h2>
        <div class="grid md:grid-cols-12 gap-4">
            @foreach([['Members and fees','Track plans, payments and outstanding balances.','members','md:col-span-7'],['Attendance','Review check-ins and attendance records.','checkin','md:col-span-5'],['WhatsApp reminders','Follow up with members about renewals.','whatsapp','md:col-span-4'],['Biometric access','Connect supported attendance hardware.','biometric','md:col-span-8'],['Trainer workspace','Manage workout and diet plans.','trainer','md:col-span-7'],['Reports','Review revenue and membership activity.','dashboard','md:col-span-5']] as [$heading,$description,$image,$span])
                <article data-reveal class="{{ $span }} border border-slate-800 rounded-xl bg-[#141518] p-6 min-h-36">
                    <h3 class="text-white mb-3">{{ $heading }}</h3><p class="text-slate-400">{{ $description }}</p>
                    @if(file_exists(public_path('images/marketing/'.$image.'.webp')))
                        <div class="mt-5 overflow-hidden rounded-xl border border-slate-800"><img src="{{ asset('images/marketing/'.$image.'.webp') }}" width="1440" height="900" loading="lazy" decoding="async" alt="{{ $heading }}" class="w-full h-auto"></div>
                    @endif
                </article>
            @endforeach
        </div>
        <a href="{{ route('features') }}" class="inline-block mt-6 text-amber-400 font-medium hover:underline">Explore features &rarr;</a>
    </div>
</section>
<section class="marketing-section marketing-light">
    <div class="mx-auto grid md:grid-cols-2 gap-8">
        <div><h2 class="mb-4">Less admin. More time for your gym.</h2><p class="text-slate-400">Keep your daily work in one place instead of switching between registers and spreadsheets.</p></div>
        <div class="border border-slate-800 rounded-xl bg-[#141518] p-6 space-y-5">
            <div class="border-b border-slate-800 pb-4"><h3>Before</h3><p class="text-slate-400 mt-2">Paper registers, separate payment records and manual renewal lists.</p></div>
            <div><h3>With GymDeskHub</h3><p class="text-slate-400 mt-2">Member profiles, attendance, collections and renewal follow-ups in one workspace.</p></div>
        </div>
    </div>
</section>
<section id="plans" class="marketing-section bg-[#141518] border-t border-slate-800">
    <div class="mx-auto">
        <h2 class="text-white mb-8">Find the right plan for your gym.</h2>
        @include('public.partials.marketing-plans')
        <a href="{{ route('pricing') }}" class="inline-block mt-6 text-amber-400 font-medium hover:underline">Compare all plans &rarr;</a>
    </div>
</section>

    {{-- ══════════════════════════════════════════════════════════════
         PROFESSIONAL REAL GYM FEEDBACK SHOWCASE
    ══════════════════════════════════════════════════════════════ --}}
    

    {{-- ══════════════════════════════════════════════════════════════
         PROFESSIONALLY ANIMATED FAQ ACCORDION (SPLIT LAYOUT - LARGE & POLISHED)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-section bg-[#0D0E10] border-t border-slate-800"><div class="mx-auto grid lg:grid-cols-12 gap-10"><h2 class="lg:col-span-4 text-white">Frequently asked questions.</h2><div class="lg:col-span-8 divide-y divide-slate-800">
@foreach([['Can I migrate my member data?','Contact us to discuss your current records and migration options.'],['Can I connect biometric devices?','Ask us about compatibility with your existing device before purchasing hardware.'],['Does WhatsApp cost extra?','WhatsApp service and messaging charges depend on the configured provider and plan.'],['Can I manage multiple branches?','Check the branch limits for your selected subscription.'],['How do I cancel?','See our cancellation and refund policy for the applicable terms.'],['What happens to my data?','See the privacy policy and contact support for data access and export questions.']] as [$question,$answer])
<details class="py-5"><summary class="cursor-pointer text-white font-medium">{{ $question }}</summary><p class="mt-3 text-slate-400">{{ $answer }}</p></details>
@endforeach
</div></div></section>

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
