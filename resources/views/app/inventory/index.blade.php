<x-app-layout header="Inventory & Supplement Stock">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Store Merchandise, Supplements & Equipment</h3>
        </div>

        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Item Name</th>
                            <th class="py-3 px-4">SKU</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Selling Price</th>
                            <th class="py-3 px-4">In Stock</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-bold text-white">{{ $item->name }}</td>
                                <td class="py-3 px-4 font-mono text-[10px] text-amber-400">{{ $item->sku ?? '—' }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $item->category ?? 'General' }}</td>
                                <td class="py-3 px-4 font-bold text-emerald-400">{{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($item->selling_price, 2) }}</td>
                                <td class="py-3 px-4 font-bold text-white">{{ $item->stock_quantity }} units</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->stock_quantity > $item->reorder_threshold ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                        {{ $item->stock_quantity > $item->reorder_threshold ? 'In Stock' : 'Low Stock' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">No inventory items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

