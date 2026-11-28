<x-guest-layout 
    title="Gym Software Pricing Plans — Simple & Transparent | GymDeskHub"
    meta-description="Transparent gym management software pricing with zero per-member charges. Annual plans with unlimited members, biometric access control sync, WhatsApp automation & 24/7 support."
    meta-keywords="gym software pricing, gym management software cost, affordable gym software india, gym erp subscription, gym software price"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION (MATTE DARK - POPPINS)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="marketing-hero marketing-light gd-pricing-hero border-b border-slate-800"><div class="mx-auto"><h1 class="mb-5">Choose a plan for <span class="gd-accent">your gym.</span></h1><p class="text-lg text-slate-400">Simple annual plans. Compare the included tools and limits before you register.</p></div></section>
<section class="marketing-section marketing-light"><div class="mx-auto">
    @include('public.partials.marketing-plans')
    @if($plans->isNotEmpty())
    <h2 class="mt-16 mb-6">Compare plans</h2>
    <p class="text-sm text-slate-400 mb-4 md:hidden">Swipe to compare all plans.</p>
    <div class="overflow-auto rounded-xl border border-slate-800 max-h-[65vh]" tabindex="0" role="region" aria-label="Plan comparison">
        <table class="w-full min-w-[620px] text-sm text-left border-collapse">
            <thead class="sticky top-0 bg-[#141518]"><tr><th scope="col" class="p-4">Feature</th>@foreach($plans as $plan)<th scope="col" class="p-4 text-center">{{ $plan->name }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-800">
                @foreach(['member_limit'=>'Members','branch_limit'=>'Branches','staff_limit'=>'Staff accounts'] as $field=>$label)
                    <tr><th scope="row" class="p-4 font-medium">{{ $label }}</th>@foreach($plans as $plan)<td class="p-4 text-center">{{ $plan->$field === -1 ? 'Unlimited' : $plan->$field }}</td>@endforeach</tr>
                @endforeach
                @foreach($plans->flatMap(fn($plan) => $plan->features)->unique('id')->sortBy('name') as $feature)
                    <tr><th scope="row" class="p-4 font-medium">{{ $feature->name }}</th>@foreach($plans as $plan)@php($value = $plan->features->firstWhere('id', $feature->id)?->pivot?->value)<td class="p-4 text-center">@if(in_array((string)$value,['1','true'],true))<span aria-label="Included">✓</span>@elseif($value === null || in_array((string)$value,['0','false',''],true))<span aria-label="Not included">—</span>@else{{ $value }}@endif</td>@endforeach</tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    <h2 class="mt-16 mb-6">Which plan fits me?</h2>
    <div class="grid md:grid-cols-3 gap-6 text-slate-400"><p><strong class="text-white">Starting out</strong><br>Check the free plan's member and staff limits.</p><p><strong class="text-white">An established gym</strong><br>Compare features for billing, trainers and reporting.</p><p><strong class="text-white">Multiple locations</strong><br>Choose a plan with enough branches and staff accounts.</p></div>
</div></section>
<section class="marketing-section marketing-light border-t border-slate-800"><div class="mx-auto grid md:grid-cols-2 gap-10"><h2 class="text-white">Questions about pricing?</h2><div class="divide-y divide-slate-800">@foreach([['How is billing calculated?','The annual plan price is shown above. Applicable taxes are confirmed during checkout.'],['Can I upgrade?','Contact support to discuss a change to your subscription.'],['What is your refund policy?','Review the cancellation and refund policy for eligibility and timelines.']] as [$question,$answer])<details class="py-4"><summary class="cursor-pointer font-medium text-white">{{ $question }}</summary><p class="mt-2 text-slate-400">{{ $answer }}</p></details>@endforeach<a href="{{ route('refund-policy') }}" class="block py-4 text-amber-400">Read refund policy</a></div></div></section>
<section class="marketing-section marketing-light border-t border-slate-800"><div class="mx-auto flex flex-wrap items-center justify-between gap-6"><h2 class="text-white">Need help choosing a plan?</h2><a href="{{ route('contact') }}" class="inline-flex items-center bg-amber-500 text-slate-950 rounded-lg px-6 py-3 font-semibold">Talk to us &rarr;</a></div></section>
</x-guest-layout>
