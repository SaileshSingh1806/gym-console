<x-guest-layout 
    title="Contact GymDeskHub — Schedule a Free Live Demo & Hardware Consultation"
    meta-description="Book a personalized live demo of GymDeskHub or get expert support for biometric access control, WhatsApp automation, and member data migration."
    meta-keywords="contact gymdeskhub, book gym software demo, biometric hardware setup gym, gym management software support"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (MATTE DARK - POPPINS)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero"><div class="mx-auto"><p class="gd-kicker">Contact</p><h1 class="mb-5">Get in <span class="gd-accent">touch.</span></h1><p class="text-lg text-slate-400">Have a question, need a demo or want to know if it fits your gym? Send us a message.</p></div></section>
<section class="marketing-section bg-[#0D0E10]" style="padding-top:0"><div class="mx-auto grid lg:grid-cols-12 gap-10 lg:gap-16 items-start">
<div class="lg:col-span-7 border border-slate-800 rounded-xl bg-[#0D0E10] p-6 sm:p-8">
<h2 class="text-white mb-6">Send a message</h2>
@if(session('contact_success'))<p role="status" class="mb-5 p-3 border border-slate-700 rounded-lg text-white">{{ session('contact_success') }}</p>@endif
@if(session('contact_error'))<p role="alert" class="mb-5 p-3 border border-slate-700 rounded-lg text-white">{{ session('contact_error') }}</p>@endif
<form method="POST" action="{{ route('contact.submit') }}" class="gd-contact-form">
@csrf
<div class="wide" hidden aria-hidden="true"><label for="website">Website</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
@foreach([['name','Your name','text',100,'name'],['gym_name','Gym name','text',150,'organization'],['phone','Phone with country code','tel',20,'tel'],['city','City','text',100,'address-level2']] as [$field,$label,$type,$max,$autocomplete])
<div><label for="{{ $field }}" class="block text-sm text-white font-medium mb-2">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" maxlength="{{ $max }}" autocomplete="{{ $autocomplete }}" required value="{{ old($field) }}" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror class="w-full px-3 py-3 rounded-lg border border-slate-700 bg-[#141518] text-white text-base">
@error($field)<p id="{{ $field }}-error" class="text-sm text-amber-400 mt-2">{{ $message }}</p>@enderror</div>
@endforeach
<div class="wide"><label for="message" class="block text-sm text-white font-medium mb-2">How can we help?</label><textarea id="message" name="message" rows="5" maxlength="1500" required class="w-full px-3 py-3 rounded-lg border border-slate-700 bg-[#141518] text-white" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>@error('message')<p id="message-error" class="text-sm text-amber-400 mt-2">{{ $message }}</p>@enderror</div>
<button type="submit" class="wide w-full sm:w-auto px-6 py-3 bg-amber-500 text-slate-950 font-semibold rounded-lg">Send message &rarr;</button>
<p class="wide text-xs text-slate-400">We use your details to respond to your inquiry. <a href="{{ route('privacy') }}" class="underline">Privacy policy</a>.</p>
</form></div>
<div class="lg:col-span-5 gd-contact-side">
@php
$contactPhone = $platformSettings['support_phone'] ?? '+91 99749 95179';
$digits = preg_replace('/[^0-9]/','',(string)$contactPhone);
$digits = strlen($digits) === 10 ? '91'.$digits : ($digits ?: '919974995179');
@endphp
<h2 class="text-white mb-6">Talk to us directly</h2>
<div class="divide-y divide-slate-800">
<div class="py-5"><p class="text-sm text-slate-400 mb-1">WhatsApp</p><a href="https://wa.me/{{ $digits }}" class="text-white font-medium hover:underline">{{ $contactPhone }}</a></div>
<div class="py-5"><p class="text-sm text-slate-400 mb-1">Phone</p><a href="tel:+{{ $digits }}" class="text-white font-medium hover:underline">{{ $contactPhone }}</a></div>
<div class="py-5"><p class="text-sm text-slate-400 mb-1">Email</p><a href="mailto:sales@gymdeskhub.in" class="text-white font-medium break-all hover:underline">sales@gymdeskhub.in</a></div>
<div class="py-5"><p class="text-sm text-slate-400 mb-1">Technical support</p><a href="mailto:support@gymdeskhub.in" class="text-white font-medium break-all hover:underline">support@gymdeskhub.in</a></div>
<div class="py-5"><p class="text-sm text-slate-400 mb-1">Business information</p><p class="text-white">GymDeskHub · Individual enterprise<br>Sailesh Singh, proprietor<br>Ahmedabad, Gujarat 380015, India</p><a href="mailto:grievance@gymdeskhub.in" class="text-amber-400 text-sm break-all">Grievance contact</a></div>
</div>
<a href="https://wa.me/{{ $digits }}?text={{ urlencode('Hi GymDeskHub, I would like to book a product demo.') }}" class="inline-flex mt-6 px-5 py-3 rounded-lg border border-slate-700 text-white font-medium">Book a 15-minute demo &rarr;</a>
</div></div></section></x-guest-layout>
