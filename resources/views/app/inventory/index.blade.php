<x-app-layout header="Inventory & Equipment Maintenance">
    <div x-data="{ 
        activeTab: '{{ $activeTab ?? 'inventory' }}',
        showAddProductModal: false,
        showEditProductModal: false,
        showAdjustStockModal: false,
        showAddEquipmentModal: false,
        showEditEquipmentModal: false,
        showRecordMaintenanceModal: false,
        selectedProduct: {},
        selectedEquipment: {},
        adjustType: 'IN',
        adjustQty: 1,
        maintenanceType: 'Routine Service',
        autoScheduleNext: true
    }" class="space-y-4">

        <!-- Top Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight leading-tight">
                        Inventory & Equipment Maintenance
                    </h1>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Supplements stock, gym machines, AC servicing, and maintenance schedules.</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button @click="showAddProductModal = true" type="button" class="flex-1 sm:flex-initial justify-center inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Product</span>
                </button>
                <button @click="showAddEquipmentModal = true" type="button" class="flex-1 sm:flex-initial justify-center inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Add Machine / AC</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards Grid (Mobile 2-cols, Desktop 4-cols) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
            <!-- Total Inventory Products -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">Total Products</span>
                    <span class="p-1 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                </div>
                <div class="mt-1.5">
                    <div class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white leading-tight">{{ number_format($totalItemsCount) }}</div>
                    <div class="flex items-center gap-1 sm:gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">
                        <span>{{ number_format($totalStockUnits) }} units</span>
                        <span>•</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $tenant?->currency_symbol ?? '₹' }}{{ number_format($totalRetailValue, 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">Low & Out Stock</span>
                    <span class="p-1 rounded-lg {{ $lowStockCount > 0 ? 'bg-red-500/10 text-red-500 dark:text-red-400 animate-pulse' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }} shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <div class="mt-1.5">
                    <div class="text-lg sm:text-xl font-bold {{ $lowStockCount > 0 ? 'text-red-500 dark:text-red-400' : 'text-slate-800 dark:text-slate-200' }} leading-tight">{{ $lowStockCount }}</div>
                    <div class="flex items-center gap-1 sm:gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">
                        <span class="text-red-500 dark:text-red-400 font-medium">{{ $outOfStockCount }} out</span>
                        <span>•</span>
                        <span>Reorder</span>
                    </div>
                </div>
            </div>

            <!-- Gym Machines & ACs -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">Machines & ACs</span>
                    <span class="p-1 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </span>
                </div>
                <div class="mt-1.5">
                    <div class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white leading-tight">{{ number_format($totalEquipmentCount) }}</div>
                    <div class="flex items-center gap-1 sm:gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">
                        <span class="text-cyan-600 dark:text-cyan-400 font-medium">{{ $acCount }} ACs</span>
                        <span>•</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $operationalCount }} OK</span>
                    </div>
                </div>
            </div>

            <!-- Maintenance Schedule Alerts -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">Service Status</span>
                    <span class="p-1 rounded-lg {{ $overdueMaintenanceCount > 0 ? 'bg-rose-500/10 text-rose-500 dark:text-rose-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' }} shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                </div>
                <div class="mt-1.5">
                    <div class="text-lg sm:text-xl font-bold {{ $overdueMaintenanceCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} leading-tight truncate">
                        {{ $overdueMaintenanceCount > 0 ? $overdueMaintenanceCount . ' Overdue' : 'All OK' }}
                    </div>
                    <div class="flex items-center gap-1 sm:gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">
                        <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $dueThisMonthCount }} due 30d</span>
                        <span>•</span>
                        <span>Auto-tracked</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (Touch Scrollable on Mobile) -->
        <div class="-mx-3 px-3 sm:mx-0 sm:px-0 overflow-x-auto no-scrollbar border-b border-slate-200 dark:border-slate-800 flex items-center gap-1 whitespace-nowrap">
            <button @click="activeTab = 'inventory'" type="button" 
                class="px-3.5 py-2 text-xs font-bold transition border-b-2 flex items-center gap-1.5 shrink-0"
                :class="activeTab === 'inventory' ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-500/5' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Store Inventory & Stock
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">{{ $totalItemsCount }}</span>
            </button>

            <button @click="activeTab = 'equipment'" type="button" 
                class="px-3.5 py-2 text-xs font-bold transition border-b-2 flex items-center gap-1.5 shrink-0"
                :class="activeTab === 'equipment' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400 bg-cyan-500/5' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                Gym Machines & AC
                @if($overdueMaintenanceCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold animate-pulse">{{ $overdueMaintenanceCount }} Due</span>
                @else
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">{{ $totalEquipmentCount }}</span>
                @endif
            </button>

            <button @click="activeTab = 'maintenance_logs'" type="button" 
                class="px-3.5 py-2 text-xs font-bold transition border-b-2 flex items-center gap-1.5 shrink-0"
                :class="activeTab === 'maintenance_logs' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-indigo-500/5' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Service History
            </button>
        </div>

        <!-- TAB 1: INVENTORY & STOCK -->
        <div x-show="activeTab === 'inventory'" class="space-y-3">
            <!-- Filter & Search Bar -->
            <form method="GET" action="{{ route('app.inventory.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-2.5 shadow-sm">
                <input type="hidden" name="tab" value="inventory">
                <div class="relative col-span-2 sm:flex-1 w-full">
                    <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="item_search" value="{{ request('item_search') }}" placeholder="Search product name, SKU, or brand..." 
                        class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>

                <div class="col-span-1 sm:w-auto">
                    <select name="item_category" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-amber-500">
                        <option value="">All Categories</option>
                        <option value="Supplements" {{ request('item_category') === 'Supplements' ? 'selected' : '' }}>Supplements</option>
                        <option value="Apparel & Wear" {{ request('item_category') === 'Apparel & Wear' ? 'selected' : '' }}>Apparel & Wear</option>
                        <option value="Beverages & Energy" {{ request('item_category') === 'Beverages & Energy' ? 'selected' : '' }}>Beverages</option>
                        <option value="Accessories & Gear" {{ request('item_category') === 'Accessories & Gear' ? 'selected' : '' }}>Accessories</option>
                        <option value="Merchandise" {{ request('item_category') === 'Merchandise' ? 'selected' : '' }}>Merchandise</option>
                        <option value="Other" {{ request('item_category') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-span-1 sm:w-auto">
                    <select name="stock_filter" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-amber-500">
                        <option value="">All Stock</option>
                        <option value="low" {{ request('stock_filter') === 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out" {{ request('stock_filter') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <div class="col-span-2 sm:col-span-auto flex items-center gap-2">
                    <button type="submit" class="flex-1 sm:flex-initial px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-lg transition border border-slate-200 dark:border-slate-700">
                        Filter
                    </button>
                    @if(request()->hasAny(['item_search', 'item_category', 'stock_filter']))
                        <a href="{{ route('app.inventory.index', ['tab' => 'inventory']) }}" class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white underline">Clear</a>
                    @endif
                </div>
            </form>

            <!-- MOBILE VIEW: Dedicated Cards (block md:hidden) -->
            <div class="block md:hidden space-y-2.5">
                @forelse($items as $item)
                    @php
                        $cost = (float) $item->cost_price;
                        $sell = (float) $item->selling_price;
                        $margin = $sell > 0 ? round((($sell - $cost) / $sell) * 100, 1) : 0;
                        $isLow = $item->stock_quantity <= $item->reorder_threshold;
                        $isOut = $item->stock_quantity <= 0;
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 shadow-sm space-y-2.5">
                        <!-- Top Row: Product Name, SKU & Status -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-xs leading-snug">{{ $item->name }}</h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-semibold">{{ $item->sku ?? 'NO-SKU' }}</span>
                                    <span class="text-slate-400">•</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        {{ $item->category ?? 'General' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                @if($isOut)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        Out of Stock
                                    </span>
                                @elseif($isLow)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        In Stock
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Mid Metrics: Price, Margin, Stock -->
                        <div class="grid grid-cols-3 gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800 text-center">
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase">Price</span>
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $tenant?->currency_symbol ?? '₹' }}{{ number_format($item->selling_price, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase">Margin</span>
                                <span class="text-xs font-semibold {{ $margin >= 30 ? 'text-emerald-600 dark:text-emerald-400' : ($margin > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                    {{ $margin }}%
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase">In Stock</span>
                                <span class="text-xs font-bold {{ $isOut ? 'text-rose-600 dark:text-rose-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }}">
                                    {{ $item->stock_quantity }} <span class="text-[9px] text-slate-400 font-normal">units</span>
                                </span>
                            </div>
                        </div>

                        <!-- Actions Row -->
                        <div class="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800">
                            <button @click="selectedProduct = {{ json_encode($item) }}; showAdjustStockModal = true;" type="button" 
                                class="px-3 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold text-xs border border-amber-500/20 transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Adjust Stock
                            </button>

                            <div class="flex items-center gap-1.5">
                                <button @click="selectedProduct = {{ json_encode($item) }}; showEditProductModal = true;" type="button" 
                                    class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition" title="Edit Item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <form action="{{ route('app.inventory.items.delete', $item->id) }}" method="POST" 
                                      data-confirm="Are you sure you want to remove item '{{ addslashes($item->name) }}' from inventory?" 
                                      data-confirm-title="Delete Inventory Item" 
                                      data-confirm-btn="Yes, Delete Item" 
                                      data-confirm-type="danger" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="Delete Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center text-slate-400 dark:text-slate-500">
                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="font-medium text-slate-700 dark:text-slate-400 text-xs">No inventory products found.</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Add supplements, apparel, and merchandise to track stock.</p>
                    </div>
                @endforelse

                @if($items->hasPages())
                    <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>

            <!-- DESKTOP VIEW: High-Density Table (hidden md:block) -->
            <div class="hidden md:block rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3.5">Item & SKU</th>
                                <th class="py-2.5 px-3.5">Category</th>
                                <th class="py-2.5 px-3.5">Cost Price</th>
                                <th class="py-2.5 px-3.5">Selling Price</th>
                                <th class="py-2.5 px-3.5">Profit Margin</th>
                                <th class="py-2.5 px-3.5">In Stock</th>
                                <th class="py-2.5 px-3.5">Status</th>
                                <th class="py-2.5 px-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                            @forelse($items as $item)
                                @php
                                    $cost = (float) $item->cost_price;
                                    $sell = (float) $item->selling_price;
                                    $margin = $sell > 0 ? round((($sell - $cost) / $sell) * 100, 1) : 0;
                                    $isLow = $item->stock_quantity <= $item->reorder_threshold;
                                    $isOut = $item->stock_quantity <= 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-2.5 px-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $item->name }}</div>
                                        <div class="text-[10px] font-mono text-amber-600 dark:text-amber-400 mt-0.5">{{ $item->sku ?? 'NO-SKU' }}</div>
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/50">
                                            {{ $item->category ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3.5 text-slate-700 dark:text-slate-300 font-medium text-xs">
                                        {{ $tenant?->currency_symbol ?? '₹' }}{{ number_format($item->cost_price, 2) }}
                                    </td>
                                    <td class="py-2.5 px-3.5 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                                        {{ $tenant?->currency_symbol ?? '₹' }}{{ number_format($item->selling_price, 2) }}
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        @if($sell > 0)
                                            <span class="text-xs font-semibold {{ $margin >= 30 ? 'text-emerald-600 dark:text-emerald-400' : ($margin > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                                {{ $margin }}%
                                            </span>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <div class="font-bold {{ $isOut ? 'text-rose-600 dark:text-rose-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }} text-xs">
                                            {{ $item->stock_quantity }} <span class="text-[10px] text-slate-400 dark:text-slate-500 font-normal">units</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500">Min: {{ $item->reorder_threshold }}</div>
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        @if($isOut)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                                Out of Stock
                                            </span>
                                        @elseif($isLow)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                In Stock
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Quick Adjust Stock -->
                                            <button @click="selectedProduct = {{ json_encode($item) }}; showAdjustStockModal = true;" type="button" 
                                                class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold text-[11px] border border-amber-500/20 transition flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                Adjust Stock
                                            </button>

                                            <!-- Edit Product -->
                                            <button @click="selectedProduct = {{ json_encode($item) }}; showEditProductModal = true;" type="button" 
                                                class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition" title="Edit Item">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>

                                            <!-- Delete Product -->
                                            <form action="{{ route('app.inventory.items.delete', $item->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to remove item '{{ addslashes($item->name) }}' from inventory?" 
                                                  data-confirm-title="Delete Inventory Item" 
                                                  data-confirm-btn="Yes, Delete Item" 
                                                  data-confirm-type="danger" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="Delete Item">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <p class="font-medium text-slate-700 dark:text-slate-400 text-xs">No inventory products found.</p>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Add supplements, apparel, and merchandise to begin tracking stock.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 2: GYM MACHINES & AC MAINTENANCE -->
        <div x-show="activeTab === 'equipment'" class="space-y-3">
            <!-- Filter & Search Bar for Equipment -->
            <form method="GET" action="{{ route('app.inventory.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-2.5 shadow-sm">
                <input type="hidden" name="tab" value="equipment">
                <div class="relative col-span-2 sm:flex-1 w-full">
                    <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="equipment_search" value="{{ request('equipment_search') }}" placeholder="Search machine, AC, serial no, brand..." 
                        class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                </div>

                <div class="col-span-1 sm:w-auto">
                    <select name="equipment_category" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-cyan-500">
                        <option value="">All Categories</option>
                        <option value="AC & HVAC" {{ request('equipment_category') === 'AC & HVAC' ? 'selected' : '' }}>AC & HVAC</option>
                        <option value="Cardio Machines" {{ request('equipment_category') === 'Cardio Machines' ? 'selected' : '' }}>Cardio</option>
                        <option value="Strength & Resistance" {{ request('equipment_category') === 'Strength & Resistance' ? 'selected' : '' }}>Strength</option>
                        <option value="Cables & Racks" {{ request('equipment_category') === 'Cables & Racks' ? 'selected' : '' }}>Cables & Racks</option>
                        <option value="Electrical & Facility" {{ request('equipment_category') === 'Electrical & Facility' ? 'selected' : '' }}>Electrical</option>
                        <option value="Audio & Video" {{ request('equipment_category') === 'Audio & Video' ? 'selected' : '' }}>Audio/Video</option>
                        <option value="Other" {{ request('equipment_category') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-span-1 sm:w-auto">
                    <select name="maintenance_filter" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-cyan-500">
                        <option value="">Status</option>
                        <option value="overdue" {{ request('maintenance_filter') === 'overdue' ? 'selected' : '' }}>🔴 Overdue</option>
                        <option value="due_soon" {{ request('maintenance_filter') === 'due_soon' ? 'selected' : '' }}>⚠️ Due 7d</option>
                        <option value="this_month" {{ request('maintenance_filter') === 'this_month' ? 'selected' : '' }}>📅 Due 30d</option>
                    </select>
                </div>

                <div class="col-span-2 sm:col-span-auto flex items-center gap-2">
                    <button type="submit" class="flex-1 sm:flex-initial px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-lg transition border border-slate-200 dark:border-slate-700">
                        Filter
                    </button>
                    @if(request()->hasAny(['equipment_search', 'equipment_category', 'maintenance_filter', 'equipment_status']))
                        <a href="{{ route('app.inventory.index', ['tab' => 'equipment']) }}" class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white underline">Clear</a>
                    @endif
                </div>
            </form>

            <!-- MOBILE VIEW: Equipment Cards (block md:hidden) -->
            <div class="block md:hidden space-y-2.5">
                @forelse($equipment as $eq)
                    @php
                        $isOverdue = $eq->is_overdue && $eq->status !== 'OUT_OF_SERVICE';
                        $isDueSoon = $eq->is_due_soon && ! $isOverdue;
                        $daysLeft = $eq->days_until_maintenance;
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 shadow-sm space-y-2.5 {{ $isOverdue ? 'ring-1 ring-rose-500/30' : '' }}">
                        <!-- Machine Header & Status -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2">
                                @if($eq->category === 'AC & HVAC')
                                    <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </span>
                                @else
                                    <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                    </span>
                                @endif
                                <div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-xs leading-snug">{{ $eq->name }}</h3>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                        @if($eq->brand)
                                            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $eq->brand }}</span>
                                        @endif
                                        @if($eq->location)
                                            <span>• {{ $eq->location }}</span>
                                        @endif
                                        @if($eq->serial_number)
                                            <span class="font-mono text-[9px] text-slate-400">S/N: {{ $eq->serial_number }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div>
                                @if($eq->status === 'OPERATIONAL')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        Operational
                                    </span>
                                @elseif($eq->status === 'MAINTENANCE_DUE')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        Service Due
                                    </span>
                                @elseif($eq->status === 'UNDER_REPAIR')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">
                                        Under Repair
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        Out of Service
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Maintenance Meta Box -->
                        <div class="grid grid-cols-2 gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase">Next Service</span>
                                @if($eq->next_service_date)
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">
                                        {{ \Carbon\Carbon::parse($eq->next_service_date)->format('d M, Y') }}
                                    </div>
                                    @if($isOverdue)
                                        <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 animate-pulse">Overdue ({{ abs($daysLeft) }}d)</span>
                                    @elseif($isDueSoon)
                                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">Due in {{ $daysLeft }}d</span>
                                    @else
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400">In {{ $daysLeft }}d</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-xs italic">—</span>
                                @endif
                            </div>

                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase">Technician</span>
                                <div class="font-medium text-slate-800 dark:text-slate-200 text-xs truncate">{{ $eq->vendor_name ?? 'Technician' }}</div>
                                @if($eq->vendor_contact)
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $eq->vendor_contact) }}" class="text-[10px] text-cyan-600 dark:text-cyan-400 font-mono underline block mt-0.5">
                                        {{ $eq->vendor_contact }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Actions Row -->
                        <div class="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800">
                            <button @click="selectedEquipment = {{ json_encode($eq) }}; showRecordMaintenanceModal = true;" type="button" 
                                class="px-3 py-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 font-bold text-xs border border-cyan-500/20 transition flex items-center gap-1 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Log Service
                            </button>

                            <div class="flex items-center gap-1.5">
                                <button @click="selectedEquipment = {{ json_encode($eq) }}; showEditEquipmentModal = true;" type="button" 
                                    class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition" title="Edit Machine">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>

                                <form action="{{ route('app.inventory.equipment.delete', $eq->id) }}" method="POST" 
                                      data-confirm="Are you sure you want to delete the equipment record for '{{ addslashes($eq->name) }}'?" 
                                      data-confirm-title="Delete Equipment Record" 
                                      data-confirm-btn="Yes, Delete Record" 
                                      data-confirm-type="danger" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="Delete Equipment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center text-slate-400 dark:text-slate-500">
                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                        <p class="font-medium text-slate-700 dark:text-slate-400 text-xs">No gym equipment or AC units registered.</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Click "Add Machine / AC" to track service schedules and repairs.</p>
                    </div>
                @endforelse

                @if($equipment->hasPages())
                    <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                        {{ $equipment->links() }}
                    </div>
                @endif
            </div>

            <!-- DESKTOP VIEW: High-Density Table (hidden md:block) -->
            <div class="hidden md:block rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3.5">Machine / AC Name</th>
                                <th class="py-2.5 px-3.5">Category & Location</th>
                                <th class="py-2.5 px-3.5">Status</th>
                                <th class="py-2.5 px-3.5">Interval</th>
                                <th class="py-2.5 px-3.5">Last Serviced</th>
                                <th class="py-2.5 px-3.5">Next Maintenance</th>
                                <th class="py-2.5 px-3.5">Vendor / Contact</th>
                                <th class="py-2.5 px-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                            @forelse($equipment as $eq)
                                @php
                                    $isOverdue = $eq->is_overdue && $eq->status !== 'OUT_OF_SERVICE';
                                    $isDueSoon = $eq->is_due_soon && ! $isOverdue;
                                    $daysLeft = $eq->days_until_maintenance;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition {{ $isOverdue ? 'bg-rose-500/5' : '' }}">
                                    <td class="py-2.5 px-3.5">
                                        <div class="flex items-center gap-2">
                                            @if($eq->category === 'AC & HVAC')
                                                <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                </span>
                                            @else
                                                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                                </span>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                                                    {{ $eq->name }}
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                                                    @if($eq->brand)
                                                        <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $eq->brand }}</span>
                                                    @endif
                                                    @if($eq->model_number)
                                                        <span>• {{ $eq->model_number }}</span>
                                                    @endif
                                                    @if($eq->serial_number)
                                                        <span class="font-mono text-[10px] text-slate-400 dark:text-slate-500">S/N: {{ $eq->serial_number }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <div class="font-medium text-slate-700 dark:text-slate-300 text-xs">{{ $eq->category }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                            {{ $eq->location ?? 'Main Area' }}
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        @if($eq->status === 'OPERATIONAL')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1 w-max">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                                Operational
                                            </span>
                                        @elseif($eq->status === 'MAINTENANCE_DUE')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1 w-max">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                                                Service Due
                                            </span>
                                        @elseif($eq->status === 'UNDER_REPAIR')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 flex items-center gap-1 w-max">
                                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 dark:bg-orange-400"></span>
                                                Under Repair
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center gap-1 w-max">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                                                Out of Service
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <span class="font-medium text-slate-700 dark:text-slate-300 text-xs">Every {{ $eq->maintenance_interval_days }}d</span>
                                    </td>
                                    <td class="py-2.5 px-3.5 text-slate-500 dark:text-slate-400 font-medium text-xs">
                                        @if($eq->last_service_date)
                                            {{ \Carbon\Carbon::parse($eq->last_service_date)->format('d M, Y') }}
                                        @else
                                            <span class="text-slate-400 dark:text-slate-600">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        @if($eq->next_service_date)
                                            <div class="font-bold text-slate-900 dark:text-white text-xs">
                                                {{ \Carbon\Carbon::parse($eq->next_service_date)->format('d M, Y') }}
                                            </div>
                                            <div class="mt-0.5">
                                                @if($isOverdue)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 animate-pulse inline-flex items-center gap-1">
                                                        Overdue ({{ abs($daysLeft) }}d)
                                                    </span>
                                                @elseif($isDueSoon)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 inline-flex items-center gap-1">
                                                        Due in {{ $daysLeft }}d
                                                    </span>
                                                @else
                                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">
                                                        In {{ $daysLeft }}d
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 text-xs italic">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        @if($eq->vendor_name || $eq->vendor_contact)
                                            <div class="font-medium text-slate-800 dark:text-slate-200 text-xs">{{ $eq->vendor_name ?? 'Technician' }}</div>
                                            @if($eq->vendor_contact)
                                                <div class="text-[11px] text-cyan-600 dark:text-cyan-400 font-mono mt-0.5">{{ $eq->vendor_contact }}</div>
                                            @endif
                                        @else
                                            <span class="text-slate-400 dark:text-slate-600">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Record Maintenance Service -->
                                            <button @click="selectedEquipment = {{ json_encode($eq) }}; showRecordMaintenanceModal = true;" type="button" 
                                                class="px-2.5 py-1 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 font-bold text-[11px] border border-cyan-500/20 transition flex items-center gap-1 shadow-sm">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Log Service
                                            </button>

                                            <!-- Edit Equipment -->
                                            <button @click="selectedEquipment = {{ json_encode($eq) }}; showEditEquipmentModal = true;" type="button" 
                                                class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition" title="Edit Machine">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>

                                            <!-- Delete Equipment -->
                                            <form action="{{ route('app.inventory.equipment.delete', $eq->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to delete the equipment record for '{{ addslashes($eq->name) }}'?" 
                                                  data-confirm-title="Delete Equipment Record" 
                                                  data-confirm-btn="Yes, Delete Record" 
                                                  data-confirm-type="danger" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="Delete Equipment">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                            <p class="font-medium text-slate-700 dark:text-slate-400 text-xs">No gym equipment or AC units registered yet.</p>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Click "Add Machine / AC" to track service schedules, AC filter cleaning, and repairs.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($equipment->hasPages())
                    <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                        {{ $equipment->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 3: SERVICE HISTORY & LOGS -->
        <div x-show="activeTab === 'maintenance_logs'" class="space-y-3">
            <!-- MOBILE VIEW: Dedicated Service Log Cards (block md:hidden) -->
            <div class="block md:hidden space-y-2.5">
                <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white">Equipment Maintenance History</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Complete log of routine maintenance, repairs, and service costs.</p>
                </div>

                @forelse($maintenanceLogs as $log)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 shadow-sm space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase">
                                    {{ \Carbon\Carbon::parse($log->service_date)->format('d M, Y') }}
                                </span>
                                <h3 class="font-bold text-slate-900 dark:text-white text-xs mt-0.5">
                                    {{ $log->equipment?->name ?? 'Equipment Deleted' }}
                                </h3>
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $log->equipment?->category }}</div>
                            </div>

                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                                {{ $log->maintenance_type }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase">Technician</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $log->technician_name ?? '—' }}</span>
                                @if($log->technician_contact)
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $log->technician_contact) }}" class="text-[10px] text-cyan-600 dark:text-cyan-400 font-mono underline block mt-0.5">
                                        {{ $log->technician_contact }}
                                    </a>
                                @endif
                            </div>

                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase">Cost & Status</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $tenant?->currency_symbol ?? '₹' }}{{ number_format($log->cost, 2) }}</span>
                                <div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $log->status_after_service }}</div>
                            </div>
                        </div>

                        @if($log->work_summary || $log->replaced_parts)
                            <div class="text-[11px] text-slate-600 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800">
                                @if($log->work_summary)
                                    <p>{{ $log->work_summary }}</p>
                                @endif
                                @if($log->replaced_parts)
                                    <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">Parts: {{ $log->replaced_parts }}</p>
                                @endif
                            </div>
                        @endif

                        @if($log->next_service_date)
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                Next Due: <span class="font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($log->next_service_date)->format('d M, Y') }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                        No maintenance logs recorded yet.
                    </div>
                @endforelse

                @if($maintenanceLogs->hasPages())
                    <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                        {{ $maintenanceLogs->links() }}
                    </div>
                @endif
            </div>

            <!-- DESKTOP VIEW: High-Density Table (hidden md:block) -->
            <div class="hidden md:block rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 dark:text-white">Equipment Maintenance & Service History</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Complete log of routine maintenance, repairs, filter replacements, and service costs.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3.5">Service Date</th>
                                <th class="py-2.5 px-3.5">Machine / AC Equipment</th>
                                <th class="py-2.5 px-3.5">Maintenance Type</th>
                                <th class="py-2.5 px-3.5">Technician / Vendor</th>
                                <th class="py-2.5 px-3.5">Service Cost</th>
                                <th class="py-2.5 px-3.5">Status After</th>
                                <th class="py-2.5 px-3.5">Work Done / Notes</th>
                                <th class="py-2.5 px-3.5">Next Service Due</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                            @forelse($maintenanceLogs as $log)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-2.5 px-3.5 font-bold text-slate-900 dark:text-white text-xs">
                                        {{ \Carbon\Carbon::parse($log->service_date)->format('d M, Y') }}
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $log->equipment?->name ?? 'Equipment Deleted' }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $log->equipment?->category }}</div>
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                                            {{ $log->maintenance_type }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <div class="font-medium text-slate-800 dark:text-slate-200 text-xs">{{ $log->technician_name ?? '—' }}</div>
                                        @if($log->technician_contact)
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $log->technician_contact }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5 font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                                        {{ $tenant?->currency_symbol ?? '₹' }}{{ number_format($log->cost, 2) }}
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                            {{ $log->status_after_service }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3.5 text-slate-700 dark:text-slate-300 max-w-xs truncate text-xs" title="{{ $log->work_summary }}">
                                        {{ $log->work_summary ?? 'Routine check & maintenance completed' }}
                                        @if($log->replaced_parts)
                                            <div class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">Parts: {{ $log->replaced_parts }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3.5 font-semibold text-slate-900 dark:text-white text-xs">
                                        @if($log->next_service_date)
                                            {{ \Carbon\Carbon::parse($log->next_service_date)->format('d M, Y') }}
                                        @else
                                            <span class="text-slate-400 dark:text-slate-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-slate-400 dark:text-slate-500 text-xs">
                                        No maintenance logs recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($maintenanceLogs->hasPages())
                    <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                        {{ $maintenanceLogs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- ================= MODALS ================= -->

        <!-- 1. ADD PRODUCT MODAL -->
        <div x-show="showAddProductModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.outside="showAddProductModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md overflow-hidden shadow-2xl text-slate-900 dark:text-slate-200">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span class="p-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        Add New Inventory Product
                    </h3>
                    <button @click="showAddProductModal = false" type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.inventory.items.store') }}" method="POST" class="p-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Optimum Nutrition 100% Whey Gold Standard 2kg"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-amber-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">SKU / Barcode</label>
                            <input type="text" name="sku" placeholder="Auto-generated"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
                            <select name="category" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                                <option value="Supplements">Supplements</option>
                                <option value="Apparel & Wear">Apparel & Wear</option>
                                <option value="Beverages & Energy">Beverages & Energy</option>
                                <option value="Accessories & Gear">Accessories & Gear</option>
                                <option value="Merchandise">Gym Merchandise</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Cost Price ({{ $tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="cost_price" required placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Selling Price ({{ $tenant?->currency_symbol ?? '₹' }}) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="selling_price" required placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Stock Units <span class="text-rose-500">*</span></label>
                            <input type="number" min="0" name="stock_quantity" value="10" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Alert Threshold <span class="text-rose-500">*</span></label>
                            <input type="number" min="0" name="reorder_threshold" value="3" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    @if($branches->count() > 1)
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Branch</label>
                            <select name="branch_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                                <option value="">All / Primary Branch</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button @click="showAddProductModal = false" type="button" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition">
                            Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. EDIT PRODUCT MODAL -->
        <div x-show="showEditProductModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.outside="showEditProductModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md overflow-hidden shadow-2xl text-slate-900 dark:text-slate-200">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span class="p-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                        Edit Product Details
                    </h3>
                    <button @click="showEditProductModal = false" type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/app/inventory/items/' + selectedProduct.id" method="POST" class="p-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Product Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required x-model="selectedProduct.name"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">SKU</label>
                            <input type="text" name="sku" x-model="selectedProduct.sku"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
                            <select name="category" required x-model="selectedProduct.category" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                                <option value="Supplements">Supplements</option>
                                <option value="Apparel & Wear">Apparel & Wear</option>
                                <option value="Beverages & Energy">Beverages & Energy</option>
                                <option value="Accessories & Gear">Accessories & Gear</option>
                                <option value="Merchandise">Gym Merchandise</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Cost Price ({{ $tenant?->currency_symbol ?? '₹' }})</label>
                            <input type="number" step="0.01" min="0" name="cost_price" required x-model="selectedProduct.cost_price"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Selling Price ({{ $tenant?->currency_symbol ?? '₹' }})</label>
                            <input type="number" step="0.01" min="0" name="selling_price" required x-model="selectedProduct.selling_price"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Low Stock Alert Threshold</label>
                        <input type="number" min="0" name="reorder_threshold" required x-model="selectedProduct.reorder_threshold"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button @click="showEditProductModal = false" type="button" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition">
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. QUICK STOCK ADJUST MODAL -->
        <div x-show="showAdjustStockModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.outside="showAdjustStockModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-sm overflow-hidden shadow-2xl text-slate-900 dark:text-slate-200">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <span class="p-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </span>
                            Adjust: <span x-text="selectedProduct.name" class="text-amber-600 dark:text-amber-400 truncate max-w-[150px] inline-block align-bottom"></span>
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Current Stock: <span x-text="selectedProduct.stock_quantity" class="font-bold text-slate-900 dark:text-white"></span> units</p>
                    </div>
                    <button @click="showAdjustStockModal = false" type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/app/inventory/items/' + selectedProduct.id + '/adjust'" method="POST" class="p-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Action</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="IN" x-model="adjustType" class="sr-only">
                                <div class="px-2 py-1.5 rounded-lg text-center text-xs font-bold transition border"
                                    :class="adjustType === 'IN' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                    + Add
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="OUT" x-model="adjustType" class="sr-only">
                                <div class="px-2 py-1.5 rounded-lg text-center text-xs font-bold transition border"
                                    :class="adjustType === 'OUT' ? 'bg-rose-500/20 border-rose-500 text-rose-600 dark:text-rose-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                    - Deduct
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="ADJUSTMENT" x-model="adjustType" class="sr-only">
                                <div class="px-2 py-1.5 rounded-lg text-center text-xs font-bold transition border"
                                    :class="adjustType === 'ADJUSTMENT' ? 'bg-amber-500/20 border-amber-500 text-amber-600 dark:text-amber-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                    = Set
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <span x-show="adjustType === 'IN'">Units to Add <span class="text-rose-500">*</span></span>
                            <span x-show="adjustType === 'OUT'">Units to Deduct <span class="text-rose-500">*</span></span>
                            <span x-show="adjustType === 'ADJUSTMENT'">Exact Total <span class="text-rose-500">*</span></span>
                        </label>
                        <input type="number" min="1" name="quantity" required x-model="adjustQty"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Reason / Note</label>
                        <input type="text" name="notes" placeholder="e.g. New stock arrived"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-amber-500">
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button @click="showAdjustStockModal = false" type="button" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition">
                            Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. ADD EQUIPMENT / AC MODAL -->
        <div x-show="showAddEquipmentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.outside="showAddEquipmentModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-lg overflow-hidden shadow-2xl text-slate-900 dark:text-slate-200">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span class="p-1 rounded-md bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                        </span>
                        Register Machine / AC Unit & Schedule
                    </h3>
                    <button @click="showAddEquipmentModal = false" type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.inventory.equipment.store') }}" method="POST" class="p-4 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Machine / Equipment Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Main Hall AC Unit 1 - Daikin 2-Ton or Treadmill #1"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
                            <select name="category" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                                <option value="AC & HVAC">AC & HVAC Units</option>
                                <option value="Cardio Machines">Cardio Machines</option>
                                <option value="Strength & Resistance">Strength & Resistance</option>
                                <option value="Cables & Racks">Cables & Racks</option>
                                <option value="Electrical & Facility">Electrical & Facility</option>
                                <option value="Audio & Video">Audio & Display</option>
                                <option value="Other">Other Equipment</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Floor / Location</label>
                            <input type="text" name="location" placeholder="e.g. Ground Floor, Studio 1"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Brand / Make</label>
                            <input type="text" name="brand" placeholder="e.g. Daikin, Technogym"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Serial Number</label>
                            <input type="text" name="serial_number" placeholder="e.g. SN-8492048"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="p-3 rounded-lg bg-cyan-50 dark:bg-cyan-500/5 border border-cyan-200 dark:border-cyan-500/20 space-y-2.5">
                        <div class="text-[11px] font-bold text-cyan-700 dark:text-cyan-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Maintenance Schedule
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Service Frequency <span class="text-rose-500">*</span></label>
                                <select name="maintenance_interval_days" required class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                                    <option value="30">Monthly (30 Days - AC Filters)</option>
                                    <option value="60">Bi-Monthly (60 Days)</option>
                                    <option value="90" selected>Quarterly (90 Days)</option>
                                    <option value="180">Semi-Annual (6 Months)</option>
                                    <option value="365">Annual (1 Year)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Service Date</label>
                                <input type="date" name="last_service_date" value="{{ date('Y-m-d') }}"
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Next Service Due (Optional - auto calculated)</label>
                            <input type="date" name="next_service_date"
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Technician / Vendor</label>
                            <input type="text" name="vendor_name" placeholder="e.g. Daikin Service"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Vendor Contact</label>
                            <input type="text" name="vendor_contact" placeholder="e.g. +91 9876543210"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                                <option value="OPERATIONAL">Operational</option>
                                <option value="MAINTENANCE_DUE">Maintenance Due</option>
                                <option value="UNDER_REPAIR">Under Repair</option>
                                <option value="OUT_OF_SERVICE">Out of Service</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Purchase Cost ({{ $tenant?->currency_symbol ?? '₹' }})</label>
                            <input type="number" step="0.01" min="0" name="purchase_cost" placeholder="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button @click="showAddEquipmentModal = false" type="button" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-lg transition">
                            Save Machine
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 5. EDIT EQUIPMENT MODAL -->
        <div x-show="showEditEquipmentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.outside="showEditEquipmentModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-lg overflow-hidden shadow-2xl text-slate-900 dark:text-slate-200">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span class="p-1 rounded-md bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </span>
                        Edit Machine / AC Details
                    </h3>
                    <button @click="showEditEquipmentModal = false" type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/app/inventory/equipment/' + selectedEquipment.id" method="POST" class="p-4 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Machine Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required x-model="selectedEquipment.name"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
                            <select name="category" required x-model="selectedEquipment.category" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                                <option value="AC & HVAC">AC & HVAC Units</option>
                                <option value="Cardio Machines">Cardio Machines</option>
                                <option value="Strength & Resistance">Strength & Resistance</option>
                                <option value="Cables & Racks">Cables & Racks</option>
                                <option value="Electrical & Facility">Electrical & Facility</option>
                                <option value="Audio & Video">Audio & Video</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Location</label>
                            <input type="text" name="location" x-model="selectedEquipment.location"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Brand</label>
                            <input type="text" name="brand" x-model="selectedEquipment.brand"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Serial Number</label>
                            <input type="text" name="serial_number" x-model="selectedEquipment.serial_number"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Interval (Days)</label>
                            <input type="number" min="1" name="maintenance_interval_days" required x-model="selectedEquipment.maintenance_interval_days"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Next Due Date</label>
                            <input type="date" name="next_service_date" x-model="selectedEquipment.next_service_date"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-rose-500">*</span></label>
                            <select name="status" required x-model="selectedEquipment.status" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                                <option value="OPERATIONAL">Operational</option>
                                <option value="MAINTENANCE_DUE">Maintenance Due</option>
                                <option value="UNDER_REPAIR">Under Repair</option>
                                <option value="OUT_OF_SERVICE">Out of Service</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Vendor Contact</label>
                            <input type="text" name="vendor_contact" x-model="selectedEquipment.vendor_contact"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button @click="showEditEquipmentModal = false" type="button" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-lg transition">
                            Update Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 6. RECORD MAINTENANCE / SERVICE MODAL -->
        <div x-show="showRecordMaintenanceModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.outside="showRecordMaintenanceModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md overflow-hidden shadow-2xl text-slate-900 dark:text-slate-200">
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <span class="p-1 rounded-md bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </span>
                            Log Service: <span x-text="selectedEquipment.name" class="text-cyan-600 dark:text-cyan-400 truncate max-w-[150px] inline-block align-bottom"></span>
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Record routine maintenance, gas refill & schedule next date.</p>
                    </div>
                    <button @click="showRecordMaintenanceModal = false" type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/app/inventory/equipment/' + selectedEquipment.id + '/maintenance'" method="POST" class="p-4 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Service Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="service_date" required value="{{ date('Y-m-d') }}"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Type <span class="text-rose-500">*</span></label>
                            <select name="maintenance_type" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                                <option value="Routine Inspection & Service">Routine Inspection</option>
                                <option value="AC Filter Cleaning & Deep Wash">AC Filter & Wash</option>
                                <option value="AC Gas Refill & Leak Repair">AC Gas Refill</option>
                                <option value="Belt / Cable Lubrication">Belt/Cable Lubrication</option>
                                <option value="Motor / Bearing Replacement">Motor/Bearing Fix</option>
                                <option value="Emergency Repair">Emergency Repair</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Technician Name</label>
                            <input type="text" name="technician_name" :value="selectedEquipment.vendor_name" placeholder="e.g. Ramesh"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Technician Phone</label>
                            <input type="text" name="technician_contact" :value="selectedEquipment.vendor_contact" placeholder="e.g. +91 9876543210"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Service Cost ({{ $tenant?->currency_symbol ?? '₹' }})</label>
                            <input type="number" step="0.01" min="0" name="cost" value="0.00"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Status After <span class="text-rose-500">*</span></label>
                            <select name="status_after_service" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500">
                                <option value="OPERATIONAL" selected>Operational</option>
                                <option value="UNDER_REPAIR">Under Repair</option>
                                <option value="MAINTENANCE_DUE">Needs Follow-up</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Work Summary</label>
                        <textarea name="work_summary" rows="2" placeholder="e.g. Cleaned filter, refilled gas"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Replaced Parts (if any)</label>
                        <input type="text" name="replaced_parts" placeholder="e.g. Capacitor 50uF"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                    </div>

                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="auto_schedule_next" value="1" checked
                                class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                            <span class="text-[11px] text-slate-700 dark:text-slate-300 font-medium">
                                Auto-schedule next maintenance (<span x-text="selectedEquipment.maintenance_interval_days || 90"></span> days)
                            </span>
                        </label>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button @click="showRecordMaintenanceModal = false" type="button" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-lg transition">
                            Record Service
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
