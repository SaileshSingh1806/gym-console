<x-app-layout header="Gym Business Settings">
    <div class="max-w-3xl space-y-8">
        <!-- Business Profile Form -->
        <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6">Business Profile & Currency</h3>

            <form action="{{ route('app.settings.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Gym Name *</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Contact Email</label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Contact Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Default Currency</label>
                        <select name="currency" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                            <option value="INR" {{ $tenant->currency === 'INR' ? 'selected' : '' }}>INR (₹) - Indian Rupee</option>
                            <option value="USD" {{ $tenant->currency === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ $tenant->currency === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ $tenant->currency === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="AED" {{ $tenant->currency === 'AED' ? 'selected' : '' }}>AED (د.إ) - UAE Dirham</option>
                            <option value="CAD" {{ $tenant->currency === 'CAD' ? 'selected' : '' }}>CAD ($) - Canadian Dollar</option>
                            <option value="AUD" {{ $tenant->currency === 'AUD' ? 'selected' : '' }}>AUD ($) - Australian Dollar</option>
                            <option value="SGD" {{ $tenant->currency === 'SGD' ? 'selected' : '' }}>SGD ($) - Singapore Dollar</option>
                            <option value="SAR" {{ $tenant->currency === 'SAR' ? 'selected' : '' }}>SAR (﷼) - Saudi Riyal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Timezone</label>
                        <input type="text" name="timezone" value="{{ old('timezone', $tenant->timezone) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Branches List Overview -->
        <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Gym Locations / Branches</h3>
            <div class="space-y-3">
                @foreach($branches as $b)
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center text-xs">
                        <div>
                            <span class="font-bold text-white block text-sm">{{ $b->name }}</span>
                            <span class="text-slate-400 text-[11px]">{{ $b->address ?? 'No address' }} • {{ $b->phone ?? 'No phone' }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $b->is_main ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-400' }}">
                            {{ $b->is_main ? 'Primary Main Branch' : 'Branch Location' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

