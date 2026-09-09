<x-app-layout header="Fitness Trainers & Coaches">
    <div class="space-y-6" x-data="{ showModal: false }">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Certified Coaching Staff</h3>
            <button @click="showModal = true" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2">
                + Add Trainer
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($trainers as $trainer)
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-amber-500/10 text-amber-400 font-bold text-sm flex items-center justify-center">
                            {{ substr($trainer->first_name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">{{ $trainer->full_name }}</h4>
                            <span class="text-xs text-amber-400 font-medium">{{ $trainer->specialization ?? 'General Fitness' }}</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-400 space-y-1 mb-4">
                        <div>Phone: {{ $trainer->phone }}</div>
                        <div>Email: {{ $trainer->email ?? 'N/A' }}</div>
                        <div>Rate: {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($trainer->hourly_rate, 2) }}/hr</div>
                    </div>
                    <p class="text-xs text-slate-500 italic">{{ $trainer->bio ?? 'No biography added.' }}</p>
                </div>
            @empty
                <div class="col-span-3 p-8 text-center bg-slate-900 border border-slate-800 rounded-2xl text-slate-500 text-xs">
                    No trainers registered yet.
                </div>
            @endforelse
        </div>

        <!-- Add Trainer Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl" @click.away="showModal = false">
                <h3 class="text-base font-bold text-white mb-4">Add Fitness Trainer</h3>
                <form action="{{ route('app.trainers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">First Name *</label>
                            <input type="text" name="first_name" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Last Name *</label>
                            <input type="text" name="last_name" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number *</label>
                        <input type="text" name="phone" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Specialization</label>
                        <input type="text" name="specialization" placeholder="e.g. Strength & Conditioning" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Hourly Rate ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }})</label>
                        <input type="number" step="0.01" name="hourly_rate" placeholder="500.00" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs">Save Trainer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

