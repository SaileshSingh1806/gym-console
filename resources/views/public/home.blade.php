<x-guest-layout 
    title="GymDeskHub™ — All-in-One Gym Management Software & Biometric Integration"
    meta-description="Manage your gym members, attendance, fee collections, and trainer workouts from one simple platform. Plug & play biometric integration and automated WhatsApp fee reminders."
    meta-keywords="gym management software, best gym software india, gym membership software, biometric integration, gym attendance system, trainer app, member app, gym billing pos, whatsapp gym reminders"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (CLEAN MATTE DARK - NO GLOW - HUMAN TONE)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero gd-hero">
        @if(file_exists(public_path('assets/img/hero-gym.webp')))<img class="gd-hero-bg" src="{{ asset('assets/img/hero-gym.webp') }}" width="2400" height="1400" alt="" fetchpriority="high" data-parallax data-speed=".15">@endif
        <div class="mx-auto gd-hero-grid">
            <div><p class="gd-kicker">Gym management software</p><h1 class="mb-6">Run your gym without the <span class="gd-accent">chaos.</span></h1><p class="text-lg text-slate-300 mb-8">Members, attendance, payments and coaching in one workspace, from the front desk to your phone.</p><div class="gd-cta-links"><a class="bg-amber-500 text-slate-950" href="{{ route('register') }}">Start free</a><a class="gd-secondary" href="#front-desk">See how it works</a></div><p class="mt-4 text-sm text-slate-400">No card needed for the free plan.</p></div>
            <div class="gd-visual"><x-marketing-visual image="hero-dashboard" alt="Owner dashboard preview" :eager="true"/>
                <div class="gd-float one" data-parallax data-speed=".10"><span aria-hidden="true">↗</span><div><strong>Renewal reminder sent</strong>on WhatsApp · Example</div></div>
                <div class="gd-float two" data-parallax data-speed=".18"><span aria-hidden="true">✓</span><div><strong>Rohan checked in</strong>6:42 am · Example</div></div>
                <div class="gd-float three" data-parallax data-speed=".28"><span aria-hidden="true">₹</span><div>Collected today<strong>₹18,400</strong>Example</div></div>
            </div>
        </div>
    </section>
    <section class="gd-proof"><div class="mx-auto">@foreach([['Gyms','Built for India'],['Members managed','Member records'],['Check-ins','Daily attendance'],['Collections','Payment records']] as [$label,$value])<div><strong>{{ $value }}</strong><span>{{ $label }}</span></div>@endforeach</div></section>
    @php($steps = [['01','A member arrives','Scan the QR at the door or enter a phone number to record a check-in.','phone-checkin','checkin'],['02','Collect a fee','Record a payment, see the balance and generate a PDF invoice.','phone-payment','payment'],['03','Follow up on renewals','Prepare a WhatsApp reminder before a membership ends.','phone-whatsapp','whatsapp'],['04','Review the day','Open the owner dashboard to review collections and attendance from anywhere.','dashboard-owner','dashboard']])
    <section id="front-desk" class="marketing-section bg-[#141518]"><div class="mx-auto"><p class="gd-kicker">Daily operations</p><h2 class="mb-6">A day at your front desk.</h2><p class="text-slate-400 mb-10">Four moments that keep your gym running.</p>
        <div class="gd-story"><div>@foreach($steps as [$number,$heading,$description,$image,$kind])<article class="gd-story-step" data-story-step="{{ $number }}"><div class="gd-story-mobile"><x-marketing-visual :image="$image" :kind="$kind" :alt="$heading"/></div><span class="gd-step-number">{{ $number }}</span><h3>{{ $heading }}</h3><p class="text-slate-400">{{ $description }}</p></article>@endforeach</div><div class="gd-story-pin" aria-hidden="true">@foreach($steps as [$number,$heading,$description,$image,$kind])<div class="gd-story-screen {{ $loop->first ? 'active' : '' }}" data-story-screen="{{ $number }}"><x-marketing-visual :image="$image" :kind="$kind" :alt="$heading"/></div>@endforeach</div></div>
    </div></section>
    <section class="gd-band">@if(file_exists(public_path('assets/img/gym-band.webp')))<img src="{{ asset('assets/img/gym-band.webp') }}" width="2400" height="1000" alt="" loading="lazy" data-parallax data-speed=".2">@endif<div class="mx-auto"><h2>Built for how Indian gyms actually run.</h2></div></section>
<section class="marketing-section bg-[#0D0E10]">
    <div class="mx-auto">
        <h2 class="text-white mb-8">Tools for every part of your gym.</h2>
        <div class="grid md:grid-cols-12 gap-4 gd-bento">
            @foreach([['Members and fees','Track plans, payments and outstanding balances.','members-list','md:col-span-7'],['Attendance','Review check-ins and attendance records.','phone-checkin','md:col-span-5'],['WhatsApp reminders','Follow up with members about renewals.','phone-whatsapp','md:col-span-4'],['Biometric access','Connect supported attendance hardware.','biometric-entry','md:col-span-8'],['Trainer workspace','Manage workout and diet plans.','trainer-tablet','md:col-span-7'],['Reports','Review revenue and membership activity.','dashboard-owner','md:col-span-5']] as [$heading,$description,$image,$span])
                <article data-reveal class="{{ $span }} border border-slate-800 rounded-xl bg-[#141518] p-6 min-h-36">
                    <h3 class="text-white mb-3">{{ $heading }}</h3><p class="text-slate-400">{{ $description }}</p>
                    @if(in_array($image,['members-list','biometric-entry','trainer-tablet','dashboard-owner'],true))<x-marketing-visual :image="$image" :kind="match($image){'members-list'=>'members','biometric-entry'=>'biometric','trainer-tablet'=>'trainer',default=>'dashboard'}" :alt="$heading"/>@endif
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
    <section class="marketing-section gd-band">@if(file_exists(public_path('assets/img/gym-cta.webp')))<img src="{{ asset('assets/img/gym-cta.webp') }}" width="2400" height="1000" alt="" loading="lazy" data-parallax data-speed=".15">@endif<div class="mx-auto flex flex-wrap items-center justify-between gap-6"><div><h2 class="text-white mb-3">Bring your gym into one workspace.</h2><p class="text-slate-400">Questions? <a href="{{ route('contact') }}" class="text-amber-400 hover:underline">Talk to us.</a></p></div><a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 rounded-lg bg-amber-500 text-slate-950 font-semibold">Start free &rarr;</a></div></section>
</x-guest-layout>
