<x-app-layout header="Operational Expenses">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Gym Operational Expenses</h3>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Title</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Payment Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($expenses as $exp)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-bold text-white">{{ $exp->title }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $exp->category->name ?? 'General Expense' }}</td>
                                <td class="py-3 px-4 font-bold text-red-400">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($exp->amount, 2) }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $exp->expense_date->format('M d, Y') }}</td>
                                <td class="py-3 px-4 uppercase text-[10px] text-slate-400 font-semibold">{{ $exp->payment_method }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">No expenses recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

