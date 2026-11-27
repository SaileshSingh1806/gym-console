<div class="grid md:grid-cols-3 gap-4 items-stretch">
@forelse($plans as $plan)
    <article class="rounded-xl p-6 flex flex-col border {{ $plan->is_popular ? 'border-amber-500' : 'border-slate-800' }} bg-[#141518]">
        <div class="flex items-center justify-between gap-2 mb-4"><h3 class="text-white">{{ ucfirst(strtolower($plan->name)) }}</h3>@if($plan->is_popular)<span class="text-xs text-amber-400">Popular</span>@endif</div>
        <div class="text-4xl font-semibold text-white tabular-nums">₹{{ number_format((float) $plan->price_yearly, 0) }}</div>
        <p class="text-sm text-slate-400 mt-1">{{ $plan->price_yearly > 0 ? 'per year + applicable taxes' : 'Free plan' }}</p>
        <ul class="text-sm text-slate-300 space-y-3 my-6">
            <li>{{ $plan->member_limit === -1 ? 'Unlimited' : number_format($plan->member_limit) }} members</li>
            <li>{{ $plan->branch_limit === -1 ? 'Unlimited branches' : $plan->branch_limit . ' branch(es)' }}</li>
            <li>{{ $plan->staff_limit === -1 ? 'Unlimited staff accounts' : $plan->staff_limit . ' staff accounts' }}</li>
            @foreach($plan->features->filter(fn($feature) => in_array((string) $feature->pivot->value, ['1','true'], true))->take(5) as $feature)
                <li class="flex gap-2"><span aria-hidden="true">✓</span>{{ $feature->name }}</li>
            @endforeach
        </ul>
        <a href="{{ route('register', ['plan' => $plan->slug]) }}" class="mt-auto inline-flex justify-center items-center rounded-lg px-4 py-3 font-semibold text-sm {{ $plan->is_popular ? 'bg-amber-500 text-slate-950' : 'border border-slate-700 text-white' }}">{{ $plan->price_yearly > 0 ? 'Choose plan' : 'Start free' }}</a>
    </article>
@empty
    <p class="text-slate-400">Contact us for current subscription options.</p>
@endforelse
</div>
