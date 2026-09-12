<x-app-layout header="Balance Sheet">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
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

        <!-- ==================== HEADER & TOP CONTROLS ==================== -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>{{ $periodLabel }}</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">Financial Overview</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Period Toggles (This Month, This Quarter, This Year) -->
                <div class="inline-flex rounded-xl bg-slate-900 border border-slate-800 p-1">
                    <a href="{{ route('app.finance.balance-sheet', ['period' => 'month', 'year' => $year, 'branch_id' => request('branch_id')]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $period === 'month' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                        This Month
                    </a>
                    <a href="{{ route('app.finance.balance-sheet', ['period' => 'quarter', 'year' => $year, 'branch_id' => request('branch_id')]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $period === 'quarter' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                        This Quarter
                    </a>
                    <a href="{{ route('app.finance.balance-sheet', ['period' => 'year', 'year' => $year, 'branch_id' => request('branch_id')]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $period === 'year' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                        This Year
                    </a>
                </div>

                <!-- Year Select -->
                <form action="{{ route('app.finance.balance-sheet') }}" method="GET" class="inline-flex">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                    <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs font-bold focus:border-indigo-500 focus:outline-none cursor-pointer">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                            <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>

                <!-- Gym Badge -->
                <div class="px-3 py-1.5 rounded-xl bg-indigo-500/15 border border-indigo-500/25 text-indigo-400 text-xs font-bold flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>{{ $tenant->name ?? 'PowerFit Gym' }}</span>
                </div>

                <!-- Branch Filter -->
                <form action="{{ route('app.finance.balance-sheet') }}" method="GET" class="inline-flex">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <select name="branch_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs font-bold focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="all" {{ empty($branchId) ? 'selected' : '' }}>🌐 All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </form>

                <!-- Download PDF / Print -->
                <a href="{{ route('app.finance.balance-sheet.pdf', request()->all()) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-emerald-600/25 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download PDF</span>
                </a>
            </div>
        </div>

        <!-- ==================== 4 TOP KPI METRIC CARDS ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Income -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden shadow-sm">
                <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span>Total Income</span>
                </div>
                <div class="text-2xl lg:text-3xl font-black text-emerald-400 tracking-tight">
                    {{ $currency }}{{ number_format($totalIncome, 2) }}
                </div>
                <div class="text-[10px] text-slate-400 font-medium mt-1">
                    Memberships: {{ $currency }}{{ number_format($membershipIncome, 2) }} | POS: {{ $currency }}{{ number_format($posSales, 2) }}
                </div>
            </div>

            <!-- 2. Total Expenses -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden shadow-sm">
                <div class="flex items-center gap-2 text-red-400 text-xs font-bold mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span>Total Expenses</span>
                </div>
                <div class="text-2xl lg:text-3xl font-black text-red-400 tracking-tight">
                    {{ $currency }}{{ number_format($totalExpenses, 2) }}
                </div>
                <div class="text-[10px] text-slate-400 font-medium mt-1">
                    {{ $categoryCount }} categories
                </div>
            </div>

            <!-- 3. Net Profit -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden shadow-sm">
                <div class="text-xs font-bold text-sky-400 mb-1">
                    Net Profit
                </div>
                <div class="text-2xl lg:text-3xl font-black text-sky-400 tracking-tight">
                    {{ $currency }}{{ number_format($netProfit, 2) }}
                </div>
                <div class="text-[10px] text-slate-400 font-medium mt-1">
                    {{ number_format($marginPercent, 1) }}% margin
                </div>
            </div>

            <!-- 4. Expense Ratio -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden shadow-sm">
                <div class="text-xs font-bold text-purple-400 mb-1">
                    % Expense Ratio
                </div>
                <div class="text-2xl lg:text-3xl font-black text-purple-400 tracking-tight">
                    {{ number_format($expenseRatio, 1) }}%
                </div>
                <div class="text-[10px] text-slate-400 font-medium mt-1">
                    of total income
                </div>
            </div>
        </div>

        <!-- ==================== 2-COLUMN BREAKDOWN: INCOME & EXPENSES ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Income Breakdown -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Income Breakdown</h3>
                    </div>
                    <span class="text-sm font-black text-emerald-400">{{ $currency }}{{ number_format($totalIncome, 2) }}</span>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Membership Fees -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                Membership Fees
                            </span>
                            <span class="font-bold text-white">{{ $currency }}{{ number_format($membershipIncome, 2) }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: {{ $membershipPercent }}%"></div>
                        </div>
                    </div>

                    <!-- POS Sales -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                                POS Sales
                            </span>
                            <span class="font-bold text-white">{{ $currency }}{{ number_format($posSales, 2) }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full bg-purple-400 rounded-full" style="width: {{ $posPercent }}%"></div>
                        </div>
                    </div>

                    <!-- Other -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                Other
                            </span>
                            <span class="font-bold text-white">{{ $currency }}{{ number_format($otherIncome, 2) }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full bg-slate-500 rounded-full" style="width: {{ $otherPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Expense Breakdown</h3>
                    </div>
                    <span class="text-sm font-black text-red-400">{{ $currency }}{{ number_format($totalExpenses, 2) }}</span>
                </div>

                @if($totalExpenses > 0 && count($itemizedExpenses) > 0)
                    <div class="space-y-3.5 text-xs">
                        @foreach($itemizedExpenses as $expName => $expAmt)
                            @php
                                $catPct = $totalExpenses > 0 ? round(($expAmt / $totalExpenses) * 100, 1) : 0;
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-300 font-medium">{{ $expName }}</span>
                                    <span class="font-bold text-white">{{ $currency }}{{ number_format($expAmt, 2) }} ({{ $catPct }}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-red-400 rounded-full" style="width: {{ $catPct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 flex flex-col items-center justify-center text-center text-slate-500 space-y-2">
                        <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span class="text-xs font-medium">No expenses recorded</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- ==================== 2-COLUMN BOTTOM: 6-MONTH TREND & TOP SUPPLIERS ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- 6-Month Trend Chart -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">6-Month Trend</h3>
                    </div>
                </div>

                <div class="h-64 w-full">
                    <canvas id="financeTrendChart"></canvas>
                </div>
            </div>

            <!-- Top Suppliers -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Top Suppliers</h3>
                    </div>
                    <a href="{{ route('app.expenses.index') }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">View All</a>
                </div>

                @if($topSuppliers->count() > 0)
                    <div class="divide-y divide-slate-800/60 text-xs">
                        @foreach($topSuppliers as $sup)
                            <div class="py-2.5 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-white">{{ $sup->title }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $sup->category->name ?? 'General' }} • {{ $sup->count }} invoices</div>
                                </div>
                                <span class="font-black text-red-400">{{ $currency }}{{ number_format($sup->total_amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 flex flex-col items-center justify-center text-center text-slate-500 space-y-3">
                        <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        <span class="text-xs font-medium">No supplier expenses</span>
                        <button type="button" @click="showExpenseModal = true" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                            + Add Supplier Expense
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- ==================== MODAL: ADD EXPENSE ==================== -->
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
                        <input type="text" name="title" required placeholder="e.g. Rent, Electricity, Dumbbells" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
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
                                @foreach($allCategories as $cat)
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
                        <label class="block text-xs font-bold text-slate-300 mb-1">Notes / Description</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes..." class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="showExpenseModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25">Record Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('financeTrendChart');
            if (ctx) {
                new Chart(ctx, {
        (function() {
            function initFinanceChart() {
                const ctx = document.getElementById('financeTrendChart');
                if (!ctx || typeof Chart === 'undefined') return;

                // Destroy any existing instance if re-initialized
                if (window.financeChartInstance) {
                    window.financeChartInstance.destroy();
                }

                window.financeChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($trendMonths),
                        datasets: [
                            {
                                label: 'Income',
                                data: @json($trendIncome),
                                backgroundColor: 'rgba(52, 211, 153, 0.85)',
                                borderRadius: 6,
                                barThickness: 16,
                            },
                            {
                                label: 'Expenses',
                                data: @json($trendExpenses),
                                backgroundColor: 'rgba(248, 113, 113, 0.85)',
                                borderRadius: 6,
                                barThickness: 16,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: '#94a3b8',
                                    font: { size: 10, weight: 'bold' },
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    usePointStyle: true,
                                }
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#ffffff',
                                bodyColor: '#cbd5e1',
                                borderColor: '#334155',
                                borderWidth: 1,
                                padding: 10,
                                cornerRadius: 10,
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', font: { size: 10 } }
                            },
                            y: {
                                grid: { color: 'rgba(51, 65, 85, 0.3)' },
                                ticks: { color: '#64748b', font: { size: 10 } }
                                ticks: { color: '#64748b', font: { size: 10 }, beginAtZero: true }
                            }
                        }
                    }
                });
            }
        });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFinanceChart);
            } else {
                initFinanceChart();
            }
        })();
    </script>
    @endpush
</x-app-layout>

