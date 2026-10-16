<x-app-layout header="">
    <div class="space-y-6" x-data="{ addContactModal: false, editContactModal: false, activeContact: {} }">
        <!-- Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-sm cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-sm cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>WhatsApp Contact Directory</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $stats['total'] }}</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Manage gym members, CRM leads, external contacts & strict marketing consent.</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="addContactModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Contact</span>
                </button>
                <a href="{{ route('app.whatsapp.contacts.import') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import CSV</span>
                </a>
                <form action="{{ route('app.whatsapp.contacts.sync-members') }}" method="POST" class="inline" onsubmit="return confirm('Sync all active gym members into WhatsApp contacts? Existing records will not be duplicated.')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer" title="Import active members into WhatsApp database">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Sync Members</span>
                    </button>
                </form>
                <form action="{{ route('app.whatsapp.contacts.sync-leads') }}" method="POST" class="inline" onsubmit="return confirm('Sync CRM leads into WhatsApp contacts?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>Sync Leads</span>
                    </button>
                </form>
                <a href="{{ route('app.whatsapp.contacts.export') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" title="Export Contacts CSV">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
            </div>
        </div>

        <!-- Consent & Type Statistics Summary Pills -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total</span>
                <div class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total']) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-500/5 border border-emerald-500/20 text-center">
                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Opted-In (Active)</span>
                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($stats['opted_in']) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-500/5 border border-amber-500/20 text-center">
                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Consent Unknown</span>
                <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-0.5">{{ number_format($stats['unknown']) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-rose-500/5 border border-rose-500/20 text-center">
                <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Opted-Out (STOP)</span>
                <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-0.5">{{ number_format($stats['opted_out']) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-blue-500/5 border border-blue-500/20 text-center">
                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Gym Members</span>
                <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-0.5">{{ number_format($stats['members']) }}</div>
            </div>
            <div class="p-3.5 rounded-xl bg-purple-500/5 border border-purple-500/20 text-center">
                <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">CRM Leads</span>
                <div class="text-xl font-black text-purple-600 dark:text-purple-400 mt-0.5">{{ number_format($stats['leads']) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <form action="{{ route('app.whatsapp.contacts.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone or city..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Type -->
                <div>
                    <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        <option value="">All Contact Types</option>
                        <option value="member" {{ request('type') === 'member' ? 'selected' : '' }}>Gym Members</option>
                        <option value="lead" {{ request('type') === 'lead' ? 'selected' : '' }}>CRM Leads</option>
                        <option value="external" {{ request('type') === 'external' ? 'selected' : '' }}>External Contacts</option>
                    </select>
                </div>

                <!-- Consent Status -->
                <div>
                    <select name="consent_status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        <option value="">All Consent Statuses</option>
                        <option value="opted_in" {{ request('consent_status') === 'opted_in' ? 'selected' : '' }}>Opted In (Eligible for Marketing)</option>
                        <option value="unknown" {{ request('consent_status') === 'unknown' ? 'selected' : '' }}>Unknown Consent</option>
                        <option value="opted_out" {{ request('consent_status') === 'opted_out' ? 'selected' : '' }}>Opted Out (STOP)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 text-white font-bold text-xs transition-colors cursor-pointer">Filter</button>
                    @if(request()->hasAny(['search', 'type', 'consent_status', 'city']))
                        <a href="{{ route('app.whatsapp.contacts.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition-colors">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Contacts Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Phone Number</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">City / Location</th>
                            <th class="py-3 px-4">Marketing Consent</th>
                            <th class="py-3 px-4">Source</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($contacts as $contact)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $contact->name }}</div>
                                    @if(!empty($contact->tags) && is_array($contact->tags))
                                        <div class="flex items-center gap-1 mt-1 flex-wrap">
                                            @foreach($contact->tags as $tag)
                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px]">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold text-slate-900 dark:text-white">
                                    {{ $contact->phone }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $contact->type === 'member' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : '' }}
                                        {{ $contact->type === 'lead' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : '' }}
                                        {{ $contact->type === 'external' ? 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' : '' }}
                                    ">
                                        {{ $contact->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    {{ $contact->city ?: '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($contact->consent_status === 'opted_in')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <span>✓</span>
                                            <span>Opted In</span>
                                        </span>
                                    @elseif($contact->consent_status === 'opted_out')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            <span>✕</span>
                                            <span>Opted Out (STOP)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            <span>?</span>
                                            <span>Unknown</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    {{ ucwords(str_replace('_', ' ', $contact->source)) }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Quick Consent Toggle -->
                                        <form action="{{ route('app.whatsapp.contacts.consent', $contact->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="consent_status" value="{{ $contact->consent_status === 'opted_in' ? 'opted_out' : 'opted_in' }}">
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" title="{{ $contact->consent_status === 'opted_in' ? 'Revoke Consent (Opt-Out)' : 'Mark as Opted-In' }}">
                                                <svg class="w-4 h-4 {{ $contact->consent_status === 'opted_in' ? 'text-amber-500' : 'text-emerald-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </button>
                                        </form>

                                        <!-- Edit Contact -->
                                        <button type="button" @click="activeContact = {{ json_encode($contact) }}; editContactModal = true" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" title="Edit Contact">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- Delete Contact -->
                                        <form action="{{ route('app.whatsapp.contacts.delete', $contact->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete contact \'{{ $contact->name }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="text-xs">No WhatsApp contacts match the current filters.</p>
                                    <button type="button" @click="addContactModal = true" class="mt-3 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs cursor-pointer">+ Add First Contact</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($contacts->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>

        <!-- Add Contact Modal -->
        <div x-show="addContactModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div @click.away="addContactModal = false" class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Add WhatsApp Contact</h3>
                    <button type="button" @click="addContactModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
                </div>

                <form action="{{ route('app.whatsapp.contacts.store') }}" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Rahul Sharma" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Mobile Number (Indian +91) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 font-bold text-slate-400 font-mono">+91</span>
                            <input type="text" name="phone" required placeholder="9876543210" maxlength="14" class="w-full pl-12 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none font-mono dark:text-white">
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">Enter 10-digit mobile number. Country code (+91) formatted automatically.</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Type</label>
                            <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                                <option value="external">External / Lead</option>
                                <option value="member">Gym Member</option>
                                <option value="lead">CRM Lead</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">City / Location</label>
                            <input type="text" name="city" placeholder="Ahmedabad" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tags (Comma-separated)</label>
                        <input type="text" name="tags" placeholder="hyrox, evening_batch, yearly_offer" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    </div>

                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 space-y-2">
                        <label class="block font-bold text-amber-800 dark:text-amber-300">WhatsApp Marketing Consent *</label>
                        <select name="consent_status" required class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 text-xs focus:ring-2 focus:ring-amber-500 outline-none dark:text-white font-semibold">
                            <option value="opted_in">Opted-In (Documented consent received)</option>
                            <option value="unknown" selected>Unknown / Unverified (Cannot receive marketing campaigns)</option>
                            <option value="opted_out">Opted-Out (STOP requested)</option>
                        </select>
                        <input type="text" name="consent_source" placeholder="Consent source (e.g. Front desk form, Registration)" class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-amber-500/30 text-[11px] outline-none dark:text-white">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="addContactModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold cursor-pointer">Save Contact</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Contact Modal -->
        <div x-show="editContactModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div @click.away="editContactModal = false" class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Edit Contact</h3>
                    <button type="button" @click="editContactModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
                </div>

                <form :action="'{{ url('/app/whatsapp/contacts') }}/' + activeContact.id" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name</label>
                        <input type="text" name="name" :value="activeContact.name" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                        <input type="text" :value="activeContact.phone" disabled class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 font-mono text-slate-500 cursor-not-allowed">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Type</label>
                            <select name="type" x-model="activeContact.type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none dark:text-white">
                                <option value="external">External</option>
                                <option value="member">Gym Member</option>
                                <option value="lead">CRM Lead</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">City</label>
                            <input type="text" name="city" :value="activeContact.city" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Marketing Consent</label>
                        <select name="consent_status" x-model="activeContact.consent_status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none dark:text-white font-bold">
                            <option value="opted_in">Opted-In</option>
                            <option value="unknown">Unknown</option>
                            <option value="opted_out">Opted-Out (STOP)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="editContactModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold cursor-pointer">Update Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

