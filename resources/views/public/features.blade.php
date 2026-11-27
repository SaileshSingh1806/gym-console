<x-guest-layout 
    title="Gym Management Software Features — Biometric Integration, WhatsApp Billing & Mobile App | GymDeskHub"
    meta-description="Explore GymDeskHub features: biometric attendance integration, automated WhatsApp fee recovery, GST-compliant POS billing, trainer workout builder & member portal."
    meta-keywords="gym software features, biometric integration gym, automated fee reminders, gym gst invoicing, gym member mobile app, gym pos software, gym workout builder, multi branch gym software"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (100% MATTE - POPPINS - HUMAN COPY)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero bg-[#0D0E10] border-b border-slate-800"><div class="mx-auto"><h1 class="text-white mb-5">Everything your gym needs to operate.</h1><p class="text-lg text-slate-400">From the first member registration to daily collections and coaching, keep your work in one place.</p></div></section>
<section class="marketing-section bg-[#141518]"><div class="mx-auto grid lg:grid-cols-12 gap-8 items-start">
@php
$sections = [
['members','Members and memberships','Keep member details, plans and renewals organized.',['Search member records','Track membership expiry','Review outstanding fees'],'A member visits the front desk to renew their membership.','members'],
['attendance','Attendance','Know who has checked in and when.',['Record front-desk attendance','Review daily logs','Check membership status'],'A member arrives for their morning workout.','checkin'],
['payments','Payments','Keep a record of fees and collections.',['Record cash and UPI payments','Review fee balances','Generate receipts'],'A member pays part of their annual fee.','members'],
['whatsapp','WhatsApp','Prepare renewal reminders and follow up with members.',['Find expiring plans','Prepare personalized reminders','Review configured messaging options'],'A membership is due for renewal this week.','whatsapp'],
['trainers','Trainers','Organize coaching and personal training.',['Assign members to trainers','Manage workout and diet plans','Track PT sessions'],'A trainer prepares a new routine for an assigned member.','trainer'],
['reports','Reports','Review business activity from the owner workspace.',['Review collections','Track attendance','Monitor membership activity'],'An owner checks the day’s activity from their phone.','dashboard'],
['biometric','Biometric integration','Connect compatible attendance devices.',['Review supported hardware','Configure device connections','Review attendance records'],'A gym wants to connect its existing access device.','biometric'],
];
@endphp
<nav aria-label="Feature navigation" class="lg:col-span-3 lg:sticky lg:top-24 flex lg:flex-col gap-2 overflow-x-auto pb-3">
@foreach($sections as [$id,$heading])<a class="shrink-0 px-3 py-2 text-sm text-slate-300 hover:text-white border-b border-slate-800" href="#{{ $id }}">{{ $heading }}</a>@endforeach
</nav>
<div class="lg:col-span-9 divide-y divide-slate-800">
@foreach($sections as [$id,$heading,$description,$benefits,$scenario,$image])
<article id="{{ $id }}" class="scroll-mt-24 py-12 first:pt-0" data-reveal>
<h2 class="text-white mb-4">{{ $heading }}</h2><p class="text-lg text-slate-300 mb-6">{{ $description }}</p>
<div class="grid md:grid-cols-2 gap-6"><ul class="space-y-3 text-slate-300">@foreach($benefits as $benefit)<li class="flex gap-3"><span aria-hidden="true" class="text-amber-400">✓</span>{{ $benefit }}</li>@endforeach</ul><div class="rounded-xl border border-slate-800 p-5 bg-[#1B1D21]"><p class="text-xs text-slate-400 mb-2">At your gym</p><p class="text-white">{{ $scenario }}</p></div></div>
@if(file_exists(public_path('images/marketing/'.$image.'.webp')))
<div class="mt-8 rounded-[20px] overflow-hidden border border-slate-800"><div data-parallax data-speed=".08"><img src="{{ asset('images/marketing/'.$image.'.webp') }}" width="1440" height="900" loading="lazy" decoding="async" class="w-full h-auto" alt="{{ $heading }} workspace"></div></div>
@endif
</article>
@endforeach
</div></div></section>
<section class="marketing-section bg-[#0D0E10]"><div class="mx-auto flex flex-wrap items-center justify-between gap-6"><h2 class="text-white">See what fits your gym.</h2><a href="{{ route('pricing') }}" class="inline-flex items-center px-6 py-3 rounded-lg bg-amber-500 text-slate-950 font-semibold">Compare plans &rarr;</a></div></section>
</x-guest-layout>
