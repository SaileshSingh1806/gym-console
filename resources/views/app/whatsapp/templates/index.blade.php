<x-app-layout header="">
    <div class="space-y-6" x-data="{ createModal: false, previewModal: false, activePreview: {} }">
        <!-- Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-sm cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="text-sm font-medium">{{ session('warning') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-sm cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-sm cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>WhatsApp Message Templates</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $stats['total'] }}</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Pre-approved Meta templates required for business-initiated bulk broadcasts and reminders.</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <form action="{{ route('app.whatsapp.templates.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-colors flex items-center gap-2 cursor-pointer" title="Fetch all templates directly from Meta WABA">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Sync from Meta</span>
                    </button>
                </form>

                <button type="button" @click="createModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Template</span>
                </button>
            </div>
        </div>

        <!-- Filter Pills Bar -->
        <div class="flex items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-x-auto">
            <div class="flex items-center gap-2">
                <a href="{{ route('app.whatsapp.templates.index') }}" class="px-3 py-1.5 rounded-xl {{ !request()->has('status') && !request()->has('category') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-xs font-bold transition-colors">All ({{ $stats['total'] }})</a>
                <a href="{{ route('app.whatsapp.templates.index', ['status' => 'APPROVED']) }}" class="px-3 py-1.5 rounded-xl {{ request('status') === 'APPROVED' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-xs font-bold transition-colors">Approved ({{ $stats['approved'] }})</a>
                <a href="{{ route('app.whatsapp.templates.index', ['status' => 'PENDING']) }}" class="px-3 py-1.5 rounded-xl {{ request('status') === 'PENDING' ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-xs font-bold transition-colors">Pending ({{ $stats['pending'] }})</a>
                <a href="{{ route('app.whatsapp.templates.index', ['status' => 'REJECTED']) }}" class="px-3 py-1.5 rounded-xl {{ request('status') === 'REJECTED' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-xs font-bold transition-colors">Rejected ({{ $stats['rejected'] }})</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('app.whatsapp.templates.index', ['category' => 'MARKETING']) }}" class="px-3 py-1.5 rounded-xl {{ request('category') === 'MARKETING' ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-xs font-bold transition-colors">Marketing</a>
                <a href="{{ route('app.whatsapp.templates.index', ['category' => 'UTILITY']) }}" class="px-3 py-1.5 rounded-xl {{ request('category') === 'UTILITY' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-xs font-bold transition-colors">Utility</a>
            </div>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($templates as $tpl)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 flex flex-col justify-between hover:border-emerald-500/50 transition-all">
                    <div>
                        <!-- Header with Status Pill -->
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-900 dark:text-white tracking-tight font-mono">{{ $tpl->name }}</h3>
                                <div class="text-[11px] text-slate-400 mt-0.5">Lang: {{ strtoupper($tpl->language) }} &bull; Category: <span class="font-bold text-slate-600 dark:text-slate-300">{{ $tpl->category }}</span></div>
                            </div>

                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider shrink-0
                                {{ $tpl->status === 'APPROVED' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                {{ $tpl->status === 'PENDING' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                                {{ $tpl->status === 'REJECTED' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                            ">
                                {{ $tpl->status }}
                            </span>
                        </div>

                        <!-- Realistic Mini Chat Bubble Preview -->
                        <div class="p-3.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-500/15 text-xs text-slate-800 dark:text-slate-200 space-y-2 whitespace-pre-line leading-relaxed">
                            @if($tpl->header_type === 'TEXT' && $tpl->header_content)
                                <div class="font-extrabold text-slate-900 dark:text-white pb-1 border-b border-emerald-500/20">{{ $tpl->header_content }}</div>
                            @elseif($tpl->header_type !== 'NONE')
                                <div class="text-[10px] font-bold text-emerald-600 bg-emerald-500/10 px-2 py-1 rounded">[{{ $tpl->header_type }} HEADER]</div>
                            @endif

                            <div class="font-normal">{{ Str::limit($tpl->body_text, 180) }}</div>

                            @if($tpl->footer_text)
                                <div class="text-[10px] text-slate-400 pt-1">{{ $tpl->footer_text }}</div>
                            @endif
                        </div>

                        @if($tpl->status === 'REJECTED' && $tpl->rejection_reason)
                            <div class="mt-2.5 p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-[11px] text-rose-600 dark:text-rose-400">
                                <strong>Meta Rejection Reason:</strong> {{ $tpl->rejection_reason }}
                            </div>
                        @endif
                    </div>

                    <!-- Footer Actions -->
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <button type="button" @click="activePreview = {{ json_encode($tpl) }}; previewModal = true" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Preview</span>
                        </button>

                        @if($tpl->status === 'APPROVED')
                            <a href="{{ route('app.whatsapp.campaigns.index', ['template_id' => $tpl->id]) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 dark:bg-slate-800 text-white font-bold text-xs hover:bg-slate-800 transition-colors">
                                Use in Campaign &rarr;
                            </a>
                        @else
                            <span class="text-[10px] text-slate-400 italic">Campaigns disabled (requires approval)</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-xs font-semibold">No message templates found matching criteria.</p>
                    <div class="mt-4 flex items-center justify-center gap-3">
                        <form action="{{ route('app.whatsapp.templates.sync') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Sync from Meta WABA</button>
                        </form>
                        <button type="button" @click="createModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold">+ Create Template</button>
                    </div>
                </div>
            @endforelse
        </div>

        @if($templates->hasPages())
            <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                {{ $templates->links() }}
            </div>
        @endif

        <!-- Full WhatsApp Phone Bubble Preview Modal -->
        <div x-show="previewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div @click.away="previewModal = false" class="bg-slate-100 dark:bg-slate-950 rounded-3xl max-w-sm w-full border border-slate-300 dark:border-slate-800 shadow-2xl overflow-hidden">
                <!-- Phone Top Bar -->
                <div class="bg-emerald-700 text-white px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold text-xs">A</div>
                        <div>
                            <div class="font-bold text-xs">ARMOUR 24-7 GYM</div>
                            <div class="text-[9px] text-emerald-200">Official Business Account ✓</div>
                        </div>
                    </div>
                    <button type="button" @click="previewModal = false" class="text-white hover:text-emerald-200 text-lg cursor-pointer">✕</button>
                </div>

                <!-- Chat Bubble Container -->
                <div class="p-4 space-y-3 min-h-[300px] flex flex-col justify-end bg-repeat" style="background-image: radial-gradient(rgba(16, 185, 129, 0.05) 1px, transparent 1px); background-size: 16px 16px;">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl rounded-tl-xs p-4 shadow-md border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 space-y-2.5">
                        <template x-if="activePreview.header_type === 'TEXT' && activePreview.header_content">
                            <div class="font-extrabold text-sm text-slate-900 dark:text-white pb-1.5 border-b border-slate-100 dark:border-slate-800" x-text="activePreview.header_content"></div>
                        </template>

                        <div class="whitespace-pre-line leading-relaxed" x-text="activePreview.body_text"></div>

                        <template x-if="activePreview.footer_text">
                            <div class="text-[10px] text-slate-400 pt-1" x-text="activePreview.footer_text"></div>
                        </template>

                        <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400 font-mono">
                            <span>10:45 AM</span>
                            <span class="text-blue-500 font-bold">✓✓</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-center">
                    <button type="button" @click="previewModal = false" class="w-full py-2 rounded-xl bg-slate-900 text-white dark:bg-slate-800 text-xs font-bold">Close Preview</button>
                </div>
            </div>
        </div>

        <!-- Create Template Modal -->
        <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div @click.away="createModal = false" class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Submit New Meta WhatsApp Template</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Submitted directly to Meta Graph API for review and approval.</p>
                    </div>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
                </div>

                <form action="{{ route('app.whatsapp.templates.store') }}" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Template Name (Lowercase alphanumeric with underscores) *</label>
                        <input type="text" name="name" required placeholder="e.g. armour_gym_yearly_offer" pattern="^[a-z0-9_]+$" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 font-mono outline-none dark:text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                                <option value="MARKETING">MARKETING (Promotions, Offers)</option>
                                <option value="UTILITY">UTILITY (Reminders, Receipts)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Language Code *</label>
                            <select name="language" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                                <option value="en">English (en)</option>
                                <option value="en_US">English (en_US)</option>
                                <option value="hi">Hindi (hi)</option>
                                <option value="gu">Gujarati (gu)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Header Type</label>
                        <select name="header_type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                            <option value="NONE">None</option>
                            <option value="TEXT">Text Title</option>
                            <option value="IMAGE">Image Header</option>
                            <option value="VIDEO">Video Header</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Header Text (if Text selected)</label>
                        <input type="text" name="header_text" placeholder="e.g. SPECIAL OFFER FROM ARMOUR 24-7 GYM" maxlength="60" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Body Text (Supports variables like @{{1}}, @{{2}}) *</label>
                        <textarea name="body_text" required rows="6" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none font-sans text-xs dark:text-white leading-relaxed">Hi {{1}},

Welcome to ARMOUR 24-7 GYM!

Our special yearly membership offer is ₹9,999.

Facilities include:
- 24/7 Gym Access
- Strength & Cardio
- HYROX Training
- Zumba & Yoga
- Steam Bath

Location: 6th Floor, Shalin Square, Hathijan, Ahmedabad.

Reply YES for membership details.
Reply STOP to unsubscribe.</textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Footer Text (Optional, max 60 chars)</label>
                        <input type="text" name="footer_text" placeholder="ARMOUR 24-7 GYM • Reply STOP to unsubscribe" maxlength="60" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none dark:text-white">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold cursor-pointer">Submit to Meta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

