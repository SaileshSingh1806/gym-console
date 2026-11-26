<x-app-layout header="Expenses">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $activePeriod = request('period', 'this_month');
        $formatExpense = static function ($amount) use ($currency) {
            $formatted = number_format(abs((float) $amount), 2, '.', '');
            [$whole, $fraction] = explode('.', $formatted);
            if ($currency === '₹' && strlen($whole) > 3) {
                $tail = substr($whole, -3);
                $head = substr($whole, 0, -3);
                $whole = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $head) . ',' . $tail;
            } else {
                $whole = explode('.', number_format(abs((float) $amount), 2, '.', ','))[0];
            }
            return ((float) $amount < 0 ? '-' : '') . $currency . $whole . '.' . $fraction;
        };
    @endphp

    <div class="space-y-5 w-full" x-data="{
        showExpenseModal: false,
        showCategoryModal: false,
        showEditModal: false,
        editData: {
            id: null,
            title: '',
            amount: '',
            expense_date: '',
            expense_category_id: '',
            payment_method: 'cash',
            branch_id: '',
            notes: ''
        },
        openEdit(exp) {
            this.editData = {
                id: exp.id,
                title: exp.title,
                amount: exp.amount,
                expense_date: exp.expense_date_raw || '',
                expense_category_id: exp.expense_category_id || '',
                payment_method: exp.payment_method || 'cash',
                branch_id: exp.branch_id || '',
                notes: exp.notes || ''
            };
            this.showEditModal = true;
        }
    }">
        <!-- Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ==================== EXECUTIVE HEADER & ACTIONS ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-1">
            <div>
                <h1 class="text-2xl sm:text-3xl font-semibold text-fg tracking-tight">Expenses</h1>
                <p class="text-sm text-fg-muted mt-1">Track and manage operational expenses.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                @if(auth()->user()->isGymOwner() || auth()->user()->hasAnyPermission(['expenses.categories', 'expenses.create']))
                <!-- Add Category -->
                <button type="button" @click="showCategoryModal = true" class="px-3.5 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg text-xs font-semibold border border-line dark:border-line flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Categories</span>
                </button>
                @endif

                <!-- Export CSV -->
                <a href="{{ route('app.expenses.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="px-3.5 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg text-xs font-semibold border border-line dark:border-line flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>

                @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('expenses.create'))
                <!-- Record Expense -->
                <button type="button" @click="showExpenseModal = true" class="px-4 py-2.5 rounded-lg bg-accent text-accent-on text-sm font-semibold flex items-center gap-2 transition-all cursor-pointer ops-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Record Expense</span>
                </button>
                @endif
            </div>
        </div>

        <!-- ==================== 4 KPI STATS CARDS ==================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- This Month -->
            <div class="p-4 sm:p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted">This Month ({{ now()->format('M Y') }})</span>
                    
                </div>
                <div class="text-xl sm:text-2xl font-semibold text-fg tabular-nums mt-1 truncate tracking-tight">
                    {{ $formatExpense($thisMonthAmount) }}
                </div>
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">
                    Last Month: {{ $formatExpense($lastMonthAmount) }}
                </p>
            </div>

            <!-- Today -->
            <div class="p-4 sm:p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line relative overflow-hidden">
                <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted">Today's Outflow</span>
                <div class="text-xl sm:text-2xl font-semibold text-fg tabular-nums mt-1 truncate tracking-tight">
                    {{ $formatExpense($todayAmount) }}
                </div>
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">
                    {{ now()->format('D, d M Y') }}
                </p>
            </div>

            <!-- Period Total -->
            <div class="p-4 sm:p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line relative overflow-hidden">
                <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted">Selected period</span>
                <div class="text-xl sm:text-2xl font-semibold text-fg tabular-nums mt-1 truncate tracking-tight">
                    {{ $formatExpense($periodAmount) }}
                </div>
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 capitalize">
                    Filter: {{ str_replace('_', ' ', $period) }}
                </p>
            </div>

            <!-- All-Time Total -->
            <div class="p-4 sm:p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line relative overflow-hidden">
                <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted">All-time expenses</span>
                <div class="text-xl sm:text-2xl font-semibold text-fg tabular-nums mt-1 truncate tracking-tight">
                    {{ $formatExpense($totalAmount) }}
                </div>
                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">
                    Across {{ $categories->count() }} Categories
                </p>
            </div>
        </div>

        <!-- ==================== THIS MONTH'S TOP EXPENSE BREAKDOWN ==================== -->
        @if($categoryBreakdown->isNotEmpty() && $thisMonthAmount > 0)
        <div class="bg-surface dark:bg-surface p-4 sm:p-5 rounded-xl border border-line dark:border-line space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-semibold text-fg dark:text-white">Monthly category breakdown</h3>
                    <p class="text-xs text-fg-muted dark:text-fg-muted">Top expense categories for {{ now()->format('F Y') }}</p>
                </div>
                <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">{{ $formatExpense($thisMonthAmount) }} Total</span>
            </div>

            <div class="space-y-3 pt-2">
                @foreach($categoryBreakdown->take(6) as $item)
                    <div class="grid grid-cols-[minmax(0,1fr)_auto] sm:grid-cols-[minmax(120px,1fr)_minmax(0,4fr)_minmax(100px,auto)_56px] items-center gap-x-4 gap-y-1 text-sm">
                        <span class="text-fg font-medium truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                        <div class="order-last col-span-2 sm:order-none sm:col-span-1 h-2.5 rounded bg-raised overflow-hidden" role="img" aria-label="{{ $item['percentage'] }}% of monthly expenses">
                            <div class="h-full rounded {{ $loop->first ? 'bg-accent' : 'bg-fg-subtle' }}" style="width: {{ max(0, min(100, (float) $item['percentage'])) }}%;"></div>
                        </div>
                        <span class="text-fg tabular-nums text-right font-medium">{{ $formatExpense($item['amount']) }}</span>
                        <span class="text-fg-muted tabular-nums text-right col-span-2 sm:col-span-1">{{ $item['percentage'] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ==================== PERIOD SELECTOR & FILTERS TOOLBAR ==================== -->
        <div class="rounded-xl bg-surface border border-line overflow-hidden">
            <div class="p-4 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="text-base font-semibold text-fg">Expense records <span class="text-xs font-normal text-fg-muted">({{ $expenses->total() }})</span></h2><span class="text-sm text-fg-muted">Period total: <strong class="text-fg tabular-nums">{{ $formatExpense($periodAmount) }}</strong></span></div>
            <!-- Period Quick Select Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 pb-1 border-b border-line dark:border-line">
                <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted mr-1">Period:</span>
                @php
                    $periods = [
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'last_3_months' => 'Last 3 Months',
                        'this_year' => 'This Year',
                        'all' => 'All Time',
                    ];
                @endphp
                @foreach($periods as $pKey => $pLabel)
                    <a href="{{ route('app.expenses.index', array_merge(request()->except(['period', 'page']), ['period' => $pKey])) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $activePeriod === $pKey ? 'bg-raised text-fg border border-line-strong' : 'text-fg-muted border border-transparent hover:bg-raised' }}">
                        {{ $pLabel }}
                    </a>
                @endforeach
            </div>

            <!-- Search, Category & Branch Filters -->
            <form action="{{ route('app.expenses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="period" value="{{ $activePeriod }}">

                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, vendor, remarks..." class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                </div>

                <div>
                    <select name="category_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                        <option value="all">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-accent text-accent-on ops-primary text-xs font-semibold transition-all cursor-pointer">Apply Filter</button>
                    <a href="{{ route('app.expenses.index') }}" class="py-2.5 px-3.5 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted border border-line dark:border-line text-xs font-semibold text-center transition-all cursor-pointer">Reset</a>
                </div>
            </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-raised border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs font-semibold">
                        <tr>
                            <th class="py-3.5 px-4">Title / Purpose</th>
                            <th class="py-3.5 px-4">Category</th>
                            <th class="py-3.5 px-4">Branch</th>
                            <th class="py-3.5 px-4">Date</th>
                            <th class="py-3.5 px-4">Mode</th>
                            <th class="py-3.5 px-4 text-right">Amount</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                        @forelse($expenses as $exp)
                            @php
                                $expDataJson = htmlspecialchars(json_encode([
                                    'id' => $exp->id,
                                    'title' => $exp->title,
                                    'amount' => $exp->amount,
                                    'expense_date_raw' => $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->toDateString() : '',
                                    'expense_category_id' => $exp->expense_category_id,
                                    'payment_method' => $exp->payment_method,
                                    'branch_id' => $exp->branch_id,
                                    'notes' => $exp->notes,
                                ]), ENT_QUOTES, 'UTF-8');
                            @endphp
                            <tr class="hover:bg-raised dark:hover:bg-raised transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-fg dark:text-white">{{ $exp->title }}</div>
                                    @if($exp->notes)
                                        <div class="text-xs text-fg-muted dark:text-fg-muted mt-0.5 max-w-xs truncate">{{ $exp->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted border border-line dark:border-line">
                                        {{ $exp->category->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-fg-muted dark:text-fg-muted">{{ $exp->branch->name ?? 'Main' }}</td>
                                <td class="py-3.5 px-4 tabular-nums text-fg-muted dark:text-fg-muted whitespace-nowrap">
                                    {{ $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->format('d M Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-1 rounded-md text-xs font-medium bg-raised text-fg-muted border border-line">
                                        {{ $exp->payment_method === 'upi' ? 'UPI' : ucfirst(str_replace('_', ' ', $exp->payment_method ?? 'other')) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="tabular-nums font-semibold text-fg text-sm">
                                        {{ $formatExpense($exp->amount) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if(auth()->user()->isGymOwner() || auth()->user()->hasAnyPermission(['expenses.edit', 'expenses.create']))
                                        <!-- Edit Button -->
                                        <button type="button" @click="openEdit({{ $expDataJson }})" class="p-1.5 rounded-lg text-fg-muted hover:text-fg hover:bg-raised transition-all cursor-pointer" title="Edit Expense">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @endif

                                        @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('expenses.delete'))
                                        <!-- Delete Button -->
                                        <form action="{{ route('app.expenses.delete', $exp->id) }}" method="POST" 
                                              data-confirm="Are you sure you want to delete this expense of {{ $formatExpense($exp->amount) }} for '{{ addslashes($exp->title) }}'?" 
                                              data-confirm-title="Delete Operational Expense" 
                                              data-confirm-btn="Yes, Delete Expense" 
                                              data-confirm-type="danger" 
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-fg-muted hover:text-rose-600 dark:text-fg-muted dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all cursor-pointer" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-fg-muted dark:text-fg-muted text-xs">
                                    <div class="max-w-xs mx-auto space-y-3">
                                        <div class="w-12 h-12 rounded-xl bg-raised dark:bg-raised flex items-center justify-center mx-auto text-fg-muted">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-fg-muted dark:text-fg-muted">No expenses recorded for this period</div>
                                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">Start tracking your gym overheads to monitor profit margins and operational cash flows.</p>
                                        </div>
                                        @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('expenses.create'))
                                        <button type="button" @click="showExpenseModal = true" class="px-4 py-2 rounded-lg bg-accent text-accent-on ops-primary text-xs font-semibold transition cursor-pointer">Record First Expense</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
            <div class="p-4 border-t border-line dark:border-line bg-raised dark:bg-slate-950/40">
                {{ $expenses->links() }}
            </div>
            @endif
        </div>

        <!-- ==================== MODAL: RECORD EXPENSE ==================== -->
        <div x-show="showExpenseModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-lg w-full max-h-[92vh] overflow-y-auto p-4 sm:p-6 space-y-4 text-fg dark:text-fg" @click.away="showExpenseModal = false">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-raised text-fg-muted flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <h3 class="text-base font-semibold text-fg dark:text-white tracking-tight">Record Operational Expense</h3>
                    </div>
                    <button type="button" @click="showExpenseModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">✕</button>
                </div>

                <form action="{{ route('app.expenses.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Expense Title / Vendor *</label>
                        <input type="text" name="title" required placeholder="e.g. November Rent, Torrent Power Bill, Trainer Salary" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Amount ({{ $currency }}) *</label>
                            <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Expense Date *</label>
                            <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Category</label>
                            <select name="expense_category_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Method *</label>
                            <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="bank_transfer">Bank Transfer (IMPS/NEFT)</option>
                                <option value="card">Debit / Credit Card</option>
                                <option value="other">Other / Cheque</option>
                            </select>
                        </div>
                    </div>

                    @if($branches->count() > 1)
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Branch</label>
                        <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="branch_id" value="{{ $branches->first()?->id }}">
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Notes / Invoice Reference</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes, receipt/bill number or details..." class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="showExpenseModal = false" class="px-4 py-2.5 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted border border-line dark:border-line text-xs font-semibold transition cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-accent text-accent-on ops-primary text-xs font-semibold transition cursor-pointer">Record Expense</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: EDIT EXPENSE ==================== -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-lg w-full max-h-[92vh] overflow-y-auto p-4 sm:p-6 space-y-4 text-fg dark:text-fg" @click.away="showEditModal = false">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-raised text-fg-muted flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <h3 class="text-base font-semibold text-fg dark:text-white tracking-tight">Edit Operational Expense</h3>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">✕</button>
                </div>

                <form :action="'{{ url('/app/expenses') }}/' + editData.id" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Expense Title / Vendor *</label>
                        <input type="text" name="title" x-model="editData.title" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Amount ({{ $currency }}) *</label>
                            <input type="number" step="0.01" name="amount" x-model="editData.amount" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Expense Date *</label>
                            <input type="date" name="expense_date" x-model="editData.expense_date" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Category</label>
                            <select name="expense_category_id" x-model="editData.expense_category_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Method *</label>
                            <select name="payment_method" x-model="editData.payment_method" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="bank_transfer">Bank Transfer (IMPS/NEFT)</option>
                                <option value="card">Debit / Credit Card</option>
                                <option value="other">Other / Cheque</option>
                            </select>
                        </div>
                    </div>

                    @if($branches->count() > 1)
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Branch</label>
                        <select name="branch_id" x-model="editData.branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="branch_id" :value="editData.branch_id">
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Notes / Invoice Reference</label>
                        <textarea name="notes" rows="2" x-model="editData.notes" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted border border-line dark:border-line text-xs font-semibold transition cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition cursor-pointer ops-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: ADD CATEGORY ==================== -->
        <div x-show="showCategoryModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-sm w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 space-y-4 text-fg dark:text-fg" @click.away="showCategoryModal = false">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <h3 class="text-base font-semibold text-fg dark:text-white tracking-tight">Add Expense Category</h3>
                    <button type="button" @click="showCategoryModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">✕</button>
                </div>

                <form action="{{ route('app.expenses.categories.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Category Name *</label>
                        <input type="text" name="name" required placeholder="e.g. AC Servicing, Supplements Stock, Music License" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="showCategoryModal = false" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted border border-line dark:border-line text-xs font-semibold transition cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-accent text-accent-on ops-primary text-xs font-semibold transition cursor-pointer">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
