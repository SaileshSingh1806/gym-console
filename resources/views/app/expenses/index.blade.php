<x-app-layout header="Expenses Management">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
    @endphp

    <div class="space-y-6" x-data="{
        showExpenseModal: false,
        showCategoryModal: false,
    }">
        <!-- Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ==================== HEADER & ACTIONS ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-red-500/15 text-red-400 border border-red-500/20 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-white tracking-tight">Gym Operational Expenses</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Track and categorize overheads, equipment, bills, and supplies</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="showCategoryModal = true" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-bold border border-slate-700 flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>+ Category</span>
                </button>

                <button type="button" @click="showExpenseModal = true" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Expense</span>
                </button>
            </div>
        </div>

        <!-- ==================== KPI STATS ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Recorded Expenses</span>
                <div class="text-2xl font-black text-red-400 mt-1">{{ $currency }}{{ number_format($totalAmount, 2) }}</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">This Month's Overheads</span>
                <div class="text-2xl font-black text-amber-400 mt-1">{{ $currency }}{{ number_format($thisMonthAmount, 2) }}</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Expense Categories</span>
                <div class="text-2xl font-black text-indigo-400 mt-1">{{ $categories->count() }}</div>
            </div>
        </div>

        <!-- ==================== FILTERS TOOLBAR ==================== -->
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('app.expenses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search expense title or note..." class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                </div>

                <div>
                    <select name="category_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="branch_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        <option value="all">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all">Filter</button>
                    <a href="{{ route('app.expenses.index') }}" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold text-center">Reset</a>
                </div>
            </form>
        </div>

        <!-- ==================== DATA TABLE ==================== -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Title / Description</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Branch</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Expense Date</th>
                            <th class="py-3 px-4">Payment Method</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($expenses as $exp)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-white">{{ $exp->title }}</div>
                                    @if($exp->notes)
                                        <div class="text-[10px] text-slate-500">{{ $exp->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $exp->category->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400">{{ $exp->branch->name ?? 'Main' }}</td>
                                <td class="py-3 px-4 font-black text-red-400">{{ $currency }}{{ number_format($exp->amount, 2) }}</td>
                                <td class="py-3 px-4 font-mono text-slate-400">{{ $exp->expense_date ? $exp->expense_date->format('d M Y') : '' }}</td>
                                <td class="py-3 px-4 uppercase text-[10px] text-slate-400 font-semibold">{{ $exp->payment_method }}</td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('app.expenses.delete', $exp->id) }}" method="POST" 
                                          data-confirm="Are you sure you want to delete this expense record ({{ $currency }}{{ number_format($exp->amount, 2) }} - {{ addslashes($exp->title) }})?" 
                                          data-confirm-title="Delete Expense Record" 
                                          data-confirm-btn="Yes, Delete Expense" 
                                          data-confirm-type="danger" 
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-all cursor-pointer" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">No expenses found matching the criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                {{ $expenses->links() }}
            </div>
        </div>

        <!-- ==================== MODAL: RECORD EXPENSE ==================== -->
        <div x-show="showExpenseModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showExpenseModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-black text-white tracking-tight">Record Operational Expense</h3>
                    <button type="button" @click="showExpenseModal = false" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <form action="{{ route('app.expenses.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Expense Title / Vendor *</label>
                        <input type="text" name="title" required placeholder="e.g. Gym Rent, Electricity Bill, Cleaning Supplies" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Amount ({{ $currency }}) *</label>
                            <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Expense Date *</label>
                            <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Category</label>
                            <select name="expense_category_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Branch</label>
                        <select name="branch_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Notes / Remarks</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes or invoice details..." class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="showExpenseModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25">Record Expense</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: ADD CATEGORY ==================== -->
        <div x-show="showCategoryModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4" @click.away="showCategoryModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-black text-white tracking-tight">Add Expense Category</h3>
                    <button type="button" @click="showCategoryModal = false" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <form action="{{ route('app.expenses.categories.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Category Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Electricity, Maintenance, Rent" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="showCategoryModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
