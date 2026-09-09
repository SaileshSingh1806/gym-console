<x-guest-layout title="Pricing Plans - Gym Console SaaS">
    <div class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h1 class="text-4xl font-extrabold text-white">Simple, Predictable SaaS Pricing</h1>
            <p class="mt-4 text-slate-400">Upgrade or downgrade anytime. No hidden charges. 14-day free trial on all plans.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $plan)
                <div class="p-8 rounded-3xl bg-slate-900 border {{ $plan->is_popular ? 'border-amber-500 shadow-xl shadow-amber-500/10' : 'border-slate-800' }} flex flex-col justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white">{{ $plan->name }}</h2>
                        <p class="text-sm text-slate-400 mt-2">{{ $plan->description }}</p>
                        <div class="my-6">
                            <span class="text-4xl font-extrabold text-white">₹{{ number_format($plan->price_monthly, 0) }}</span>
                            <span class="text-slate-400 text-sm">/month</span>
                        </div>

                        <ul class="space-y-3 text-sm text-slate-300 mb-8">
                            <li class="font-bold text-amber-400">Limits:</li>
                            <li class="ml-2">• {{ $plan->member_limit === -1 ? 'Unlimited' : $plan->member_limit }} Members</li>
                            <li class="ml-2">• {{ $plan->branch_limit }} Branch Location{{ $plan->branch_limit > 1 ? 's' : '' }}</li>
                            <li class="ml-2">• {{ $plan->staff_limit }} Staff Logins</li>
                            <li class="font-bold text-amber-400 mt-4">Features:</li>
                            @foreach($plan->features as $feature)
                                <li class="ml-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ $feature->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <a href="{{ route('register', ['plan' => $plan->slug]) }}" class="w-full py-3 rounded-xl text-center font-bold {{ $plan->is_popular ? 'bg-amber-500 text-slate-950 hover:bg-amber-400' : 'bg-slate-800 text-white hover:bg-slate-700' }} transition-colors">
                        Choose {{ $plan->name }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-guest-layout>

