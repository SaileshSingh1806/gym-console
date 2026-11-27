<x-guest-layout 
    title="About GymDeskHub — Powering Fitness Entrepreneurs & Gyms Across India"
    meta-description="Learn about GymDeskHub's mission to streamline gym operations, biometric access control, and financial automation for fitness club owners and studios."
    meta-keywords="about gymdeskhub, gym management system team, fitness software founders, gym automation platform india"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (MATTE DARK - POPPINS)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero bg-[#0D0E10] border-b border-slate-800"><div class="mx-auto grid lg:grid-cols-2 gap-10 items-end"><div><h1 class="text-white mb-5">Built for the people who run gyms.</h1><p class="text-lg text-slate-300">GymDeskHub is built around the everyday work of Indian gym owners and their teams.</p></div><div class="border-l border-slate-700 pl-6"><p class="text-slate-400">Memberships, fee collection, attendance and coaching should be easy to manage, whether you operate one location or several.</p></div></div></section>
<section class="marketing-section bg-[#141518]"><div class="mx-auto grid lg:grid-cols-12 gap-10 items-start"><h2 class="lg:col-span-5 text-white">Why we built GymDeskHub.</h2><div class="lg:col-span-7 space-y-5 text-lg text-slate-300"><p>Running a gym means looking after members, trainers and payments every day. Separate registers and spreadsheets make it difficult to see what needs attention.</p><p>We built GymDeskHub to bring these tasks into one workspace, with tools for owners, front-desk teams, trainers and members.</p>@if(file_exists(public_path('images/marketing/dashboard.webp')))<img src="{{ asset('images/marketing/dashboard.webp') }}" width="1440" height="900" loading="lazy" decoding="async" alt="GymDeskHub owner workspace" class="w-full h-auto rounded-[20px] border border-slate-700">@endif</div></div></section>
<section class="marketing-section bg-[#0D0E10]"><div class="mx-auto grid lg:grid-cols-12 gap-8"><h2 class="lg:col-span-4 text-white">Our principles.</h2><div class="lg:col-span-8 divide-y divide-slate-800">@foreach([['Simple','Make everyday work easier for owners and staff.'],['Honest','Show clear plans, limits and features so you can decide what fits.'],['Direct support','Give gym owners a way to discuss their setup and ask for help.']] as [$heading,$description])<article class="py-6 first:pt-0" data-reveal><h3 class="text-white mb-2">{{ $heading }}</h3><p class="text-slate-400">{{ $description }}</p></article>@endforeach</div></div></section>
<section class="marketing-section py-14 sm:py-16 bg-[#141518] border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-[#1B1D21] border border-slate-800 p-8 sm:p-10 ">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-normal">
                            <span>Business &amp; Legal Ownership</span>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-semibold text-white">
                            GymDeskHub
                        </h3>
                        <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl">
                            <strong>GymDeskHub</strong> is an individual enterprise / sole proprietorship legally owned and operated by <strong class="text-white font-semibold">Sailesh Singh</strong>.
                        </p>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-400 space-y-2 border-t md:border-t-0 md:border-l border-slate-800 pt-4 md:pt-0 md:pl-8 shrink-0">
                        <p><span class="text-slate-500 font-medium">Business Constitution:</span> <strong class="text-slate-200">Individual / Sole Proprietorship</strong></p>
                        <p><span class="text-slate-500 font-medium">Proprietor / Legal Owner:</span> <strong class="text-white font-semibold">Sailesh Singh</strong></p>
                        <p><span class="text-slate-500 font-medium">Operating City:</span> <strong class="text-slate-200">Ahmedabad, Gujarat, India</strong></p>
                        <p><span class="text-slate-500 font-medium">Official Contact:</span> <strong class="text-slate-200">+91 99749 95179 &bull; support@gymdeskhub.com</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
<section class="marketing-section bg-[#0D0E10]"><div class="mx-auto flex flex-wrap gap-6 items-center justify-between"><h2 class="text-white">Tell us about your gym.</h2><a href="{{ route('contact') }}" class="inline-flex px-6 py-3 rounded-lg bg-amber-500 text-slate-950 font-semibold">Talk to us &rarr;</a></div></section></x-guest-layout>
