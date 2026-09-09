<x-app-layout header="Payments & Member Receipts">
    <div x-data="{ showModal: false }">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Member Transactions & Invoices</h3>
            <button @click="showModal = true" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2">
                + Collect Member Payment
            </button>
        </div>

        <!-- Payments Table -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4">Receipt #</th>
                            <th class="py-3.5 px-4">Member</th>
                            <th class="py-3.5 px-4">Amount</th>
                            <th class="py-3.5 px-4">Method</th>
                            <th class="py-3.5 px-4">Date</th>
                            <th class="py-3.5 px-4">Received By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($payments as $p)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-mono text-amber-400 font-bold">
                                    {{ $p->invoice_number }}
                                </td>
                                <td class="py-3 px-4 font-bold text-white">
                                    {{ $p->member->full_name }}
                                </td>
                                <td class="py-3 px-4 font-extrabold text-white">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($p->amount, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $p->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400">
                                    {{ $p->payment_date->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-400">
                                    {{ $p->receivedBy->name ?? 'Front Desk Staff' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">No payment receipts logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

        <!-- Payment Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl" @click.away="showModal = false">
                <h3 class="text-base font-bold text-white mb-4">Record Member Payment</h3>
                <form action="{{ route('app.payments.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Member *</label>
                        <select name="member_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Amount ({{ auth()->user()->tenant?->currency_symbol ?? '₹' }}) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="1500.00" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                            <option value="cash">Cash</option>
                            <option value="card">Credit/Debit Card (POS)</option>
                            <option value="upi">UPI / QR Code</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs">Record & Issue Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

