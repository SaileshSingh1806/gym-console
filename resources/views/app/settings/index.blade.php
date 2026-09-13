<x-app-layout header="Settings">
    @php
        $tenantSettings = $tenant->settings ?? [];
        $activeTab = session('active_tab', request('tab', 'business_info'));
        $gymLogo = $tenant->logo_url;
        $operatingHours = $tenantSettings['operating_hours'] ?? [
            'monday' => ['is_open' => true, 'open' => '06:00', 'close' => '22:00'],
            'tuesday' => ['is_open' => true, 'open' => '06:00', 'close' => '22:00'],
            'wednesday' => ['is_open' => true, 'open' => '06:00', 'close' => '22:00'],
            'thursday' => ['is_open' => true, 'open' => '06:00', 'close' => '22:00'],
            'friday' => ['is_open' => true, 'open' => '06:00', 'close' => '22:00'],
            'saturday' => ['is_open' => true, 'open' => '06:00', 'close' => '22:00'],
            'sunday' => ['is_open' => false, 'open' => '07:00', 'close' => '13:00'],
        ];
        $currentYearShort = now()->format('y');
        $memberCount = $tenant->members()->count() + 1;
        $branchQuota = $branchQuota ?? app(\App\Services\FeatureGateService::class)->checkQuota($tenant, 'branches');
    @endphp

    <div x-data="{
        tab: '{{ $activeTab }}',
        logoPreview: '{{ $gymLogo ?? '' }}',
        idFormat: '{{ $tenant->member_id_format ?? 'coded' }}',
        idPrefix: '{{ $tenant->member_id_prefix ?? 'GYM' }}',
        idPadding: {{ (int) ($tenant->member_id_padding ?? 4) }},
        yearShort: '{{ $currentYearShort }}',
        memberSeq: '{{ str_pad($memberCount, (int) ($tenant->member_id_padding ?? 4), '0', STR_PAD_LEFT) }}',
        showAddBranchModal: false,
        showEditBranchModal: false,
        showAddDeviceModal: false,
        copiedEndpoint: false,
        copiedToken: null,
        editBranchData: { id: '', name: '', code: '', phone: '', email: '', address: '', city: '', state: '', postal_code: '', status: 'ACTIVE', is_main: false },
        openEditBranch(b) {
            this.editBranchData = { ...b, is_main: Boolean(b.is_main) };
            this.showEditBranchModal = true;
        },
        copyText(text, key) {
            navigator.clipboard.writeText(text);
            if (key === 'endpoint') {
                this.copiedEndpoint = true;
                setTimeout(() => this.copiedEndpoint = false, 2000);
            } else {
                this.copiedToken = key;
                setTimeout(() => this.copiedToken = null, 2000);
            }
        },
        get formattedSeq() {
            let num = {{ $memberCount }};
            return String(num).padStart(this.idPadding, '0');
        },
        get fullPreview() {
            if (this.idFormat === 'numeric') {
                return String(1000 + {{ $memberCount }});
            }
            let p = (this.idPrefix || 'GYM').toUpperCase().trim();
            return p + this.yearShort + this.formattedSeq;
        },
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }" class="space-y-6 w-full">

        <!-- ==================== TOP HORIZONTAL NAVIGATION TABS ==================== -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-800 text-xs font-bold">
            <!-- 1. Business Info -->
            <button type="button" @click="tab = 'business_info'"
                    :class="tab === 'business_info' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Business Info</span>
            </button>

            <!-- 2. Operating Hours -->
            <button type="button" @click="tab = 'operating_hours'"
                    :class="tab === 'operating_hours' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Operating Hours</span>
            </button>

            <!-- 3. General & GST -->
            <button type="button" @click="tab = 'general_gst'"
                    :class="tab === 'general_gst' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>General &amp; GST</span>
            </button>

            <!-- 4. Member App -->
            <button type="button" @click="tab = 'member_app'"
                    :class="tab === 'member_app' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                <span>Member App</span>
            </button>

            <!-- 5. Member ID -->
            <button type="button" @click="tab = 'member_id'"
                    :class="tab === 'member_id' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                <span>Member ID</span>
            </button>

            <!-- 6. Branches & Locations -->
            <button type="button" @click="tab = 'branches'"
                    :class="tab === 'branches' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Branches &amp; Locations</span>
                <span class="px-1.5 py-0.5 rounded-full bg-slate-800 text-[10px] text-slate-300 font-mono">{{ $branches->count() }}/{{ $branchQuota['limit'] == -1 ? '∞' : $branchQuota['limit'] }}</span>
            </button>

            <!-- 7. Biometric & IoT Devices -->
            <button type="button" @click="tab = 'devices'"
                    :class="tab === 'devices' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                <span>Biometric &amp; IoT Devices</span>
                <span class="px-1.5 py-0.5 rounded-full bg-slate-800 text-[10px] text-slate-300 font-mono">{{ $devices->count() }}</span>
            </button>

            <!-- 8. Email & SMTP Settings -->
            <button type="button" @click="tab = 'email_smtp'"
                    :class="tab === 'email_smtp' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
                    class="px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Email &amp; SMTP</span>
                @if(!empty($tenantSettings['smtp']['enabled']))
                    <span class="px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[9px] font-bold">ACTIVE</span>
                @endif
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: BUSINESS INFO (Matching Screenshot 1) -->
        <!-- ========================================================================= -->
        <div x-show="tab === 'business_info'" x-cloak class="space-y-6">
            <form action="{{ route('app.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" value="business_info">

                <!-- Business Details Card -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-slate-800/80 pb-3.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="text-sm font-extrabold text-white tracking-wide">Business Details</h3>
                    </div>

                    <!-- 3 Grid: Gym Name, Email, Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Gym Name *</label>
                            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                                   placeholder="e.g. PowerFit Gym"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                                   placeholder="info@powerfitgym.com"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                                   placeholder="080 6940 9814"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Address</label>
                        <textarea name="address" rows="2"
                                  placeholder="123 Fitness Street, Health City"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors font-mono">{{ old('address', $tenant->address ?? $tenant->mainBranch?->address) }}</textarea>
                    </div>

                    <!-- Business Logo Upload Section -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Business Logo</label>
                        
                        <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <!-- Logo Thumbnail Box -->
                                <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-700/80 flex items-center justify-center p-1.5 overflow-hidden shadow-inner shrink-0">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview" alt="Business Logo" class="max-w-full max-h-full object-contain rounded-lg">
                                    </template>
                                    <template x-if="!logoPreview">
                                        <div class="text-2xl text-slate-600">🏢</div>
                                    </template>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold text-white">Upload new business logo</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Supports PNG, JPG, or JPEG. Max size 2MB.</p>
                                    <p class="text-[10px] text-indigo-400 mt-0.5">This logo is displayed on your sidebar, reports, invoices, and receipts.</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="file" id="logo_file_input" name="logo" accept="image/png, image/jpeg, image/jpg, image/webp" @change="previewImage($event)" class="hidden">
                                <button type="button" onclick="document.getElementById('logo_file_input').click()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-bold flex items-center gap-1.5 border border-slate-700 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Select Image</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end pt-2 border-t border-slate-800/80">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                            Save Business Details
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: OPERATING HOURS -->
        <!-- ========================================================================= -->
        <div x-show="tab === 'operating_hours'" x-cloak class="space-y-6">
            <form action="{{ route('app.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" value="operating_hours">

                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-slate-800/80 pb-3.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white tracking-wide">Operating Hours &amp; Schedule</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Configure weekly opening and closing schedules for your facility</p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-800/80 text-xs">
                        @foreach(['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'] as $key => $dayName)
                            @php
                                $dayConfig = $operatingHours[$key] ?? ['is_open' => true, 'open' => '06:00', 'close' => '22:00'];
                            @endphp
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3 w-40">
                                    <input type="checkbox" name="operating_hours[{{ $key }}][is_open]" value="1" {{ !empty($dayConfig['is_open']) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-0 focus:outline-none bg-slate-950 border-slate-800">
                                    <span class="font-bold text-white">{{ $dayName }}</span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-400 text-[11px]">Opens:</span>
                                        <input type="time" name="operating_hours[{{ $key }}][open]" value="{{ $dayConfig['open'] ?? '06:00' }}" class="px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <span class="text-slate-500">&mdash;</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-400 text-[11px]">Closes:</span>
                                        <input type="time" name="operating_hours[{{ $key }}][close]" value="{{ $dayConfig['close'] ?? '22:00' }}" class="px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end pt-2 border-t border-slate-800/80">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                            Save Operating Hours
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: GENERAL & GST -->
        <!-- ========================================================================= -->
        <div x-show="tab === 'general_gst'" x-cloak class="space-y-6">
            <form action="{{ route('app.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" value="general_gst">

                <!-- GST Configuration -->
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-slate-800/80 pb-3.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white tracking-wide">GST &amp; Tax Configuration</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Configure your tax identification number and tax invoicing rates</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-950 border border-slate-800">
                        <input type="checkbox" id="gst_reg_toggle" name="gst_registered" value="1" {{ $tenant->is_gst_registered ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-0 focus:outline-none bg-slate-900 border-slate-700">
                        <label for="gst_reg_toggle" class="text-xs font-bold text-white cursor-pointer">
                            Gym is GST Registered
                            <span class="block text-[11px] font-normal text-slate-400 mt-0.5">Enable to print GSTIN and tax breakup on invoices and statements</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">GST Number (GSTIN)</label>
                            <input type="text" name="gst_number" value="{{ old('gst_number', $tenant->gst_number) }}"
                                   placeholder="Leave blank if not registered"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-indigo-500 focus:outline-none transition-colors uppercase">
                            <p class="text-[10px] text-slate-500 mt-1">If left blank, reports and statements will not display a dummy GSTIN.</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Default GST Rate (%)</label>
                            <input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate', $tenant->gst_rate) }}"
                                   placeholder="18.00"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                        </div>
                    </div>

                    <!-- Currency & Regional Defaults -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Default Currency</label>
                            <select name="currency" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                <option value="INR" {{ $tenant->currency === 'INR' ? 'selected' : '' }}>INR (₹) &mdash; Indian Rupee</option>
                                <option value="USD" {{ $tenant->currency === 'USD' ? 'selected' : '' }}>USD ($) &mdash; US Dollar</option>
                                <option value="EUR" {{ $tenant->currency === 'EUR' ? 'selected' : '' }}>EUR (€) &mdash; Euro</option>
                                <option value="GBP" {{ $tenant->currency === 'GBP' ? 'selected' : '' }}>GBP (£) &mdash; British Pound</option>
                                <option value="AED" {{ $tenant->currency === 'AED' ? 'selected' : '' }}>AED (د.إ) &mdash; UAE Dirham</option>
                                <option value="CAD" {{ $tenant->currency === 'CAD' ? 'selected' : '' }}>CAD (CA$) &mdash; Canadian Dollar</option>
                                <option value="AUD" {{ $tenant->currency === 'AUD' ? 'selected' : '' }}>AUD (AU$) &mdash; Australian Dollar</option>
                                <option value="SGD" {{ $tenant->currency === 'SGD' ? 'selected' : '' }}>SGD (SG$) &mdash; Singapore Dollar</option>
                                <option value="SAR" {{ $tenant->currency === 'SAR' ? 'selected' : '' }}>SAR (﷼) &mdash; Saudi Riyal</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Timezone</label>
                            <input type="text" name="timezone" value="{{ old('timezone', $tenant->timezone ?? 'Asia/Kolkata') }}"
                                   placeholder="Asia/Kolkata"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2 border-t border-slate-800/80">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                            Save Tax &amp; Regional Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 4: MEMBER APP -->
        <!-- ========================================================================= -->
        <div x-show="tab === 'member_app'" x-cloak class="space-y-6">
            <form action="{{ route('app.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" value="member_app">

                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-slate-800/80 pb-3.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white tracking-wide">Member App &amp; Self-Service Portal</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Customize what your gym members see on their mobile portal</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Welcome Banner Message</label>
                        <input type="text" name="app_welcome_message" value="{{ old('app_welcome_message', $tenantSettings['app_welcome_message'] ?? 'Welcome to ' . $tenant->name . '!') }}"
                               placeholder="Welcome to our fitness community!"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-colors">
                    </div>

                    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-950 border border-slate-800">
                        <input type="checkbox" id="allow_checkin_toggle" name="allow_member_portal_checkin" value="1" {{ !empty($tenantSettings['allow_member_portal_checkin']) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-0 focus:outline-none bg-slate-900 border-slate-700">
                        <label for="allow_checkin_toggle" class="text-xs font-bold text-white cursor-pointer">
                            Allow QR Code Self Check-in
                            <span class="block text-[11px] font-normal text-slate-400 mt-0.5">Members can scan door turnstiles / entrance QR directly from mobile</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end pt-2 border-t border-slate-800/80">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                            Save Member App Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 5: MEMBER ID (Matching Screenshot 3) -->
        <!-- ========================================================================= -->
        <div x-show="tab === 'member_id'" x-cloak class="space-y-6">
            <form action="{{ route('app.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" value="member_id">

                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-6">
                    <!-- Title & Header -->
                    <div class="flex items-center gap-2.5 border-b border-slate-800/80 pb-3.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white tracking-wide">Member ID Format</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Configure how new member IDs are generated. Existing member IDs are never changed.</p>
                        </div>
                    </div>

                    <!-- ID FORMAT: 2 Selectable Cards -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">ID FORMAT</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- Card 1: Coded -->
                            <div @click="idFormat = 'coded'"
                                 :class="idFormat === 'coded' ? 'border-indigo-500 bg-indigo-950/20 ring-1 ring-indigo-500' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700'"
                                 class="p-4 rounded-2xl border flex items-center justify-between cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-extrabold text-white">Coded</div>
                                        <div class="text-xs text-slate-400">Prefix + Year + Sequence</div>
                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5">e.g. GYM{{ $currentYearShort }}0001</div>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full flex items-center justify-center"
                                     :class="idFormat === 'coded' ? 'bg-indigo-600 text-white' : 'border border-slate-700'">
                                    <svg x-show="idFormat === 'coded'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>

                            <!-- Card 2: Numeric -->
                            <div @click="idFormat = 'numeric'"
                                 :class="idFormat === 'numeric' ? 'border-indigo-500 bg-indigo-950/20 ring-1 ring-indigo-500' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700'"
                                 class="p-4 rounded-2xl border flex items-center justify-between cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-base">
                                        #
                                    </div>
                                    <div>
                                        <div class="text-sm font-extrabold text-white">Numeric</div>
                                        <div class="text-xs text-slate-400">Simple sequential number</div>
                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5">e.g. 1001, 1002, 1003 ...</div>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full flex items-center justify-center"
                                     :class="idFormat === 'numeric' ? 'bg-indigo-600 text-white' : 'border border-slate-700'">
                                    <svg x-show="idFormat === 'numeric'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>

                        </div>
                        <input type="hidden" name="member_id_format" :value="idFormat">
                    </div>

                    <!-- Prefix & Padding Controls -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">ID PREFIX</label>
                            <input type="text" name="member_id_prefix" x-model="idPrefix" maxlength="10"
                                   :disabled="idFormat === 'numeric'"
                                   placeholder="GYM"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono uppercase focus:border-indigo-500 focus:outline-none transition-colors disabled:opacity-50">
                            <p class="text-[10px] text-slate-500 mt-1">Alphanumeric, max 10 characters. Example: BF, GYM, FIT</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">NUMBER PADDING (DIGITS)</label>
                            <select name="member_id_padding" x-model="idPadding"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                <option value="3">3 digits (001)</option>
                                <option value="4">4 digits (0001)</option>
                                <option value="5">5 digits (00001)</option>
                                <option value="6">6 digits (000001)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Next Member ID Preview Box -->
                    <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 text-center space-y-4">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">NEXT MEMBER ID PREVIEW</span>
                        
                        <div class="text-3xl font-black text-white font-mono tracking-wider" x-text="fullPreview"></div>

                        <!-- Color-coded pills -->
                        <template x-if="idFormat === 'coded'">
                            <div class="flex items-center justify-center gap-2">
                                <span class="px-2.5 py-1 rounded-md bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 text-xs font-bold font-mono" x-text="(idPrefix || 'GYM').toUpperCase()"></span>
                                <span class="px-2.5 py-1 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold font-mono" x-text="yearShort"></span>
                                <span class="px-2.5 py-1 rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold font-mono" x-text="formattedSeq"></span>
                            </div>
                        </template>

                        <template x-if="idFormat === 'coded'">
                            <div class="flex items-center justify-center gap-5 text-[10px] text-slate-400">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-400"></span> Prefix</span>
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Year (auto)</span>
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Sequence</span>
                            </div>
                        </template>
                    </div>

                    <!-- Explanatory Callout -->
                    <div class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <p class="leading-relaxed">
                            <strong>How it works:</strong> Each new member gets an ID combining your prefix + current 2-digit year + an auto-incrementing sequence number. Changing the prefix starts a fresh sequence from 1. Existing members keep their original IDs regardless of format changes.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end pt-2 border-t border-slate-800/80">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                            Save ID Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 6: BRANCHES & LOCATIONS                                               -->
        <!-- ========================================================================= -->
        <div x-show="tab === 'branches'" x-cloak class="space-y-6">
            <!-- Quota & Header Banner -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black text-white">Branch Locations</h3>
                            <span class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 text-[10px] font-extrabold uppercase font-mono">
                                {{ $branchQuota['current'] }} / {{ $branchQuota['limit'] == -1 ? 'Unlimited' : $branchQuota['limit'] }} Locations Used
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Manage multiple gym facility branches, primary location, addresses &amp; contact info</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    @if($branchQuota['allowed'])
                        <button type="button" @click="showAddBranchModal = true" 
                                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/20 transition-all cursor-pointer whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Add New Branch</span>
                        </button>
                    @else
                        <a href="{{ route('app.subscription.index') }}" 
                           class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-amber-500/20 transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Upgrade Plan for More Branches</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Branches List / Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($branches as $b)
                @php
                    $isAllowed = in_array($b->id, $allowedBranchIds ?? []);
                    $isActiveSession = (session('active_branch_id') == $b->id || (empty(session('active_branch_id')) && $b->is_main)) && $isAllowed;
                @endphp
                <div class="p-5 rounded-2xl bg-slate-900 border {{ ! $isAllowed ? 'border-amber-500/20 bg-slate-900/60 opacity-80' : ($b->is_main ? 'border-amber-500/40 bg-gradient-to-b from-slate-900 via-slate-900 to-amber-950/10' : 'border-slate-800') }} shadow-xl space-y-4 flex flex-col justify-between">
                    <div>
                        <!-- Header & Badges -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-black {{ $isAllowed ? 'text-white' : 'text-slate-400' }}">{{ $b->name }}</h4>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 font-mono text-[10px] font-bold">{{ $b->code ?? 'MAIN' }}</span>
                                </div>
                                <div class="flex items-center gap-2 pt-0.5 flex-wrap">
                                    @if($b->is_main)
                                        <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-extrabold uppercase tracking-wider flex items-center gap-1">
                                            <span>★</span> Primary Branch
                                        </span>
                                    @endif
                                    @if(! $isAllowed)
                                        <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-extrabold uppercase tracking-wider flex items-center gap-1">
                                            🔒 Plan Locked
                                        </span>
                                    @elseif($b->status === 'ACTIVE')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 border border-red-500/30 text-[10px] font-bold">Inactive</span>
                                    @endif
                                    @if($isActiveSession)
                                        <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 text-[10px] font-bold">Current Working Branch</span>
                                    @endif
                                </div>
                            </div>

                            <div class="w-9 h-9 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center shrink-0 border border-slate-700/60 font-black text-xs">
                                {{ strtoupper(substr($b->name, 0, 2)) }}
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="mt-4 pt-3 border-t border-slate-800/80 space-y-2 text-xs text-slate-300">
                            @if($b->address || $b->city)
                            <div class="flex items-start gap-2 text-slate-400">
                                <svg class="w-4 h-4 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $b->address ? $b->address . ', ' : '' }}{{ $b->city ?? '' }} {{ $b->state ? '('.$b->state.')' : '' }} {{ $b->postal_code ?? '' }}</span>
                            </div>
                            @endif

                            @if($b->phone)
                            <div class="flex items-center gap-2 text-slate-400">
                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $b->phone }}</span>
                            </div>
                            @endif

                            @if($b->email)
                            <div class="flex items-center gap-2 text-slate-400">
                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>{{ $b->email }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
                        <div>
                            @if(! $isAllowed)
                                <a href="{{ route('app.subscription.index') }}" class="px-3 py-1.5 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-400 text-xs font-bold transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>Upgrade to Activate</span>
                                </a>
                            @elseif(! $isActiveSession)
                                <form action="{{ route('app.branches.switch', $b->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                                        <span>⇄ Switch Here</span>
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-emerald-400 font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active Working Branch
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="openEditBranch({{ json_encode($b) }})" 
                                    class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 text-xs font-bold transition-all cursor-pointer">
                                Edit
                            </button>

                            @if(! $b->is_main && $branches->count() > 1)
                            <form action="{{ route('app.branches.delete', $b->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to remove branch '{{ addslashes($b->name) }}'? Devices and staff associated with this location will need to be reassigned." 
                                  data-confirm-title="Remove Branch Location" 
                                  data-confirm-btn="Yes, Remove Branch" 
                                  data-confirm-type="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition-all cursor-pointer" title="Delete Branch">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full p-8 text-center bg-slate-900 border border-slate-800 rounded-2xl text-slate-500">
                    No branches found.
                </div>
                @endforelse
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 7: BIOMETRIC & IOT ACCESS CONTROL                                     -->
        <!-- ========================================================================= -->
        <div x-show="tab === 'devices'" x-cloak class="space-y-6">
            <!-- Header Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/15 text-teal-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white tracking-wide">Biometric &amp; IoT Access Control</h3>
                        <p class="text-xs text-slate-400">Connect eSSL Biometric machines (via Desktop Middleware), Hikvision terminals, turnstiles, and ZKTeco push</p>
                    </div>
                </div>
                <button type="button" @click="showAddDeviceModal = true"
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-teal-600/25 transition-all cursor-pointer whitespace-nowrap self-start md:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Register Biometric Device</span>
                </button>
            </div>

            <!-- Desktop Middleware & Push API Integration Box -->
            <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950/40 border border-indigo-500/20 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800/80 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Attendance Sync Middleware &amp; Cloud Webhook</h4>
                            <p class="text-[11px] text-slate-400">Sync fingerprint / facial punches directly into live attendance and membership access control</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 self-start sm:self-auto">
                        ● Cloud Endpoint Active
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Webhook URL Box -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Universal Webhook / Push URL</span>
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-mono text-teal-400">
                            <span class="truncate">{{ url('/api/v1/devices/events') }}</span>
                            <button type="button" @click="copyText('{{ url('/api/v1/devices/events') }}', 'endpoint')" 
                                    class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs transition-colors shrink-0" title="Copy Push URL">
                                <span x-show="!copiedEndpoint">Copy</span>
                                <span x-show="copiedEndpoint" class="text-emerald-400 font-bold" x-cloak>Copied!</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-500">Configure this URL in your eSSL desktop sync agent, Hikvision alert stream, or ZKTeco ADMS settings.</p>
                    </div>

                    <!-- Middleware Compatibility Badges -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Supported Hardware Protocols</span>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-300 font-medium flex items-center gap-1.5">
                                🖥️ eSSL Desktop Agent (LAN/USB)
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-300 font-medium flex items-center gap-1.5">
                                📹 Hikvision ISAPI / MinMoe
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-300 font-medium flex items-center gap-1.5">
                                ⚡ ZKTeco / Realtime ADMS
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-300 font-medium flex items-center gap-1.5">
                                💳 RFID &amp; Dynamic QR Readers
                            </span>
                        </div>
                        <p class="text-[10px] text-indigo-300/80">Every valid punch automatically checks membership validity, branch enrollment, and marks attendance.</p>
                    </div>
                </div>

                <!-- Step-by-step Desktop Middleware Instructions -->
                <div x-data="{ showSteps: false }" class="border-t border-slate-800/80 pt-3">
                    <button type="button" @click="showSteps = !showSteps" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5 cursor-pointer">
                        <span x-text="showSteps ? 'Hide eSSL Desktop Middleware Setup Instructions' : 'View eSSL Desktop Middleware Setup Instructions (3 Quick Steps)'"></span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': showSteps }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="showSteps" x-cloak class="mt-3 p-4 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-slate-300 space-y-2.5">
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">1</span>
                            <div>
                                <strong class="text-white">Connect Device to Local Network:</strong> Connect your eSSL or Hikvision terminal to your gym's Wi-Fi router / LAN switch. Assign a static IP address to the machine.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">2</span>
                            <div>
                                <strong class="text-white">Register Device in Gym Console:</strong> Click "+ Register Biometric Device" above and obtain the generated <em>Device Secret Token</em>.
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">3</span>
                            <div>
                                <strong class="text-white">Configure Auto-Push Agent:</strong> In your eSSL desktop sync software (eTimeTrack / CAMS / Cloud Push utility), set the cloud push URL to <code class="text-teal-400">{{ url('/api/v1/devices/events') }}</code> and paste the Device Secret Token in HTTP Header <code class="text-amber-400">X-Device-Secret</code>. Members punching on the biometric terminal will be authenticated and marked present in real-time!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Connected Hardware Devices Grid -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Registered Biometric Terminals ({{ $devices->count() }})</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($devices as $device)
                        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between space-y-4 hover:border-slate-700 transition-all">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center font-bold text-sm">
                                            @if($device->type === 'essl_desktop')
                                                🖥️
                                            @elseif(str_contains($device->type, 'turnstile'))
                                                🚧
                                            @elseif(str_contains($device->type, 'facial'))
                                                📹
                                            @elseif(str_contains($device->type, 'zkteco'))
                                                ⚡
                                            @elseif($device->type === 'rfid_reader')
                                                💳
                                            @else
                                                📱
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-white text-sm">{{ $device->name }}</h4>
                                            <span class="text-[11px] text-slate-400">
                                                @if($device->type === 'essl_desktop')
                                                    eSSL Biometric (Desktop Middleware)
                                                @elseif($device->type === 'hikvision_facial')
                                                    Hikvision Facial Terminal (ISAPI)
                                                @elseif($device->type === 'hikvision_turnstile')
                                                    Hikvision Turnstile Barrier Gate
                                                @elseif($device->type === 'zkteco_biometric')
                                                    ZKTeco / Realtime Biometric Push
                                                @elseif($device->type === 'rfid_reader')
                                                    RFID Card / NFC Scanner
                                                @elseif($device->type === 'qr_scanner')
                                                    Dynamic QR Code Scanner
                                                @else
                                                    {{ ucwords(str_replace('_', ' ', $device->type)) }}
                                                @endif
                                                • {{ $device->branch->name ?? 'Main Branch' }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $device->status === 'ONLINE' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                        ● {{ $device->status }}
                                    </span>
                                </div>

                                <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800/80 text-xs space-y-2 text-slate-300">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Connection Mode:</span>
                                        <span class="font-mono text-slate-200">
                                            @if($device->ip_address)
                                                {{ $device->ip_address }}:{{ $device->port }}
                                            @else
                                                Desktop Middleware Push
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Direction:</span>
                                        <span class="uppercase font-bold text-[11px] {{ $device->direction === 'in' ? 'text-emerald-400' : ($device->direction === 'out' ? 'text-rose-400' : 'text-amber-400') }}">
                                            {{ $device->direction === 'both' ? 'Bi-Directional (Entry & Exit)' : ($device->direction === 'in' ? 'Entry Only' : 'Exit Only') }}
                                        </span>
                                    </div>
                                    @if($device->serial_number)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Serial Number:</span>
                                        <span class="font-mono text-slate-300 text-[11px]">{{ $device->serial_number }}</span>
                                    </div>
                                    @endif
                                    <div class="flex items-center justify-between pt-1 border-t border-slate-800/60">
                                        <span class="text-slate-500">Device Secret:</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono text-[10px] text-slate-400 truncate max-w-[140px]">{{ $device->device_secret }}</span>
                                            <button type="button" @click="copyText('{{ $device->device_secret }}', 'dev_{{ $device->id }}')"
                                                    class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] transition-colors" title="Copy Secret">
                                                <span x-show="copiedToken !== 'dev_{{ $device->id }}'">Copy</span>
                                                <span x-show="copiedToken === 'dev_{{ $device->id }}'" class="text-emerald-400 font-bold" x-cloak>✓</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
                                <form action="{{ route('app.devices.test', $device->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-all cursor-pointer">
                                        Ping Test
                                    </button>
                                </form>
                                <form action="{{ route('app.devices.delete', $device->id) }}" method="POST" 
                                      data-confirm="Are you sure you want to remove biometric device '{{ addslashes($device->name) }}'? Automated check-in sync from this hardware terminal will be stopped." 
                                      data-confirm-title="Remove Biometric Device" 
                                      data-confirm-btn="Yes, Remove Device" 
                                      data-confirm-type="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition-all cursor-pointer" title="Delete Device">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 p-8 text-center bg-slate-900 border border-slate-800 rounded-2xl text-slate-500 text-xs space-y-2">
                            <p class="font-medium text-slate-400">No biometric hardware terminals registered yet.</p>
                            <p>Click <strong class="text-teal-400 cursor-pointer" @click="showAddDeviceModal = true">+ Register Biometric Device</strong> to connect your eSSL fingerprint machines or Hikvision turnstiles.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Biometric Access & Attendance Logs Table -->
            <div class="rounded-3xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Real-Time Access Control &amp; Biometric Punch Telemetry</h4>
                        <p class="text-[11px] text-slate-400">Live feed of device punches, membership access decisions, and turnstile events</p>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono">Showing latest {{ $accessLogs->count() }} records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Event Time</th>
                                <th class="py-3 px-4">Member / User</th>
                                <th class="py-3 px-4">Device Terminal</th>
                                <th class="py-3 px-4">Direction</th>
                                <th class="py-3 px-4">Access Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($accessLogs as $log)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">
                                        {{ $log->event_time ? $log->event_time->format('d M Y, h:i:s A') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($log->member)
                                            <div class="font-bold text-white">{{ $log->member->full_name }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $log->member->member_code }}</div>
                                        @else
                                            <span class="text-slate-400 font-medium">Guest / Unknown Credential</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-300">
                                        {{ $log->device->name ?? 'Direct Biometric Sync' }}
                                    </td>
                                    <td class="py-3.5 px-4 uppercase font-semibold text-amber-400 text-[11px]">
                                        {{ $log->event_type ?? 'PUNCH' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $log->access_status === 'GRANTED' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                            {{ $log->access_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">No hardware access logs recorded yet. Punches will appear here in real time.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 8: EMAIL & SMTP SETTINGS                                              -->
        <!-- ========================================================================= -->
        @php
            $smtpData = $tenantSettings['smtp'] ?? [];
            $smtpEnabled = !empty($smtpData['enabled']) || !empty($smtpData['smtp_enabled']);
        @endphp
        <div x-show="tab === 'email_smtp'" x-cloak class="space-y-6" x-data="{
            customSmtpEnabled: {{ $smtpEnabled ? 'true' : 'false' }},
            showPassword: false
        }">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main SMTP Config Form -->
                <div class="lg:col-span-2 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-white tracking-wide">Gym Outbound SMTP Configuration</h3>
                                <p class="text-xs text-slate-400">Send transactional emails (member welcome, payment receipts, renewals) from your own custom domain email</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('app.settings.update') }}" method="POST" class="space-y-5 text-xs">
                        @csrf
                        <input type="hidden" name="active_tab" value="email_smtp">

                        <!-- Toggle Custom SMTP -->
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-white mb-0.5">Enable Custom Gym SMTP Server</h4>
                                <p class="text-[11px] text-slate-400">
                                    When enabled, member notifications and receipts are sent using your own mail server. If disabled, platform default server is used.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="smtp_enabled" value="1" x-model="customSmtpEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <div x-show="customSmtpEnabled" x-transition class="space-y-4 pt-2">
                            <!-- Host, Port, Encryption -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">SMTP Host *</label>
                                    <input type="text" name="mail_host" value="{{ old('mail_host', $smtpData['mail_host'] ?? '') }}" placeholder="e.g. smtp.gmail.com"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none font-mono">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">SMTP Port *</label>
                                    <input type="number" name="mail_port" value="{{ old('mail_port', $smtpData['mail_port'] ?? 587) }}" placeholder="587 / 465"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none font-mono">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">Encryption Protocol *</label>
                                    <select name="mail_encryption" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none cursor-pointer">
                                        <option value="tls" {{ ($smtpData['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587 - Recommended)</option>
                                        <option value="ssl" {{ ($smtpData['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                        <option value="none" {{ ($smtpData['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None (Unencrypted)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Username & Password -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">SMTP Username / Email *</label>
                                    <input type="text" name="mail_username" value="{{ old('mail_username', $smtpData['mail_username'] ?? '') }}" placeholder="e.g. notifications@yourgym.com"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none">
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-1.5">
                                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">SMTP Password / App Password</label>
                                        <button type="button" @click="showPassword = !showPassword" class="text-[10px] text-slate-400 hover:text-white" x-text="showPassword ? 'Hide' : 'Show'"></button>
                                    </div>
                                    <input :type="showPassword ? 'text' : 'password'" name="mail_password" value="{{ old('mail_password', $smtpData['mail_password'] ?? '') }}" placeholder="••••••••••••••••"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none font-mono">
                                </div>
                            </div>

                            <!-- From Address & From Name -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">From Email Address *</label>
                                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $smtpData['mail_from_address'] ?? ($tenant->email ?? '')) }}" placeholder="e.g. contact@yourgym.com"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">From Sender Name *</label>
                                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $smtpData['mail_from_name'] ?? ($tenant->name ?? '')) }}" placeholder="e.g. PowerFit Gym"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/25 transition-all cursor-pointer">
                                Save Gym SMTP Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Test Connection & Setup Guide Side Cards -->
                <div class="space-y-6">
                    <!-- Test Connection Card -->
                    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                        <div class="border-b border-slate-800 pb-3">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span>🚀</span> Test Gym SMTP Mail
                            </h4>
                            <p class="text-[11px] text-slate-400">Send an instant test email to verify your mail server configuration and logo branding.</p>
                        </div>

                        <form action="{{ route('app.settings.email.test') }}" method="POST" class="space-y-4 text-xs">
                            @csrf
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1.5">Recipient Email Address</label>
                                <input type="email" name="test_email" value="{{ auth()->user()->email }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-indigo-500 focus:outline-none">
                            </div>

                            <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer border border-slate-700">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Send Test Email</span>
                            </button>
                        </form>
                    </div>

                    <!-- Setup Guide Box -->
                    <div class="p-5 rounded-3xl bg-slate-900/60 border border-slate-800/80 space-y-3">
                        <h4 class="text-xs font-bold text-white flex items-center gap-2">
                            <span>💡</span> Quick SMTP Setup Tips
                        </h4>
                        <ul class="text-[11px] text-slate-400 space-y-2 leading-relaxed">
                            <li class="flex items-start gap-1.5">
                                <span class="text-indigo-400 font-bold">•</span>
                                <span><strong>Gmail / Google Workspace:</strong> Host: <code class="text-indigo-300">smtp.gmail.com</code>, Port: <code class="text-indigo-300">587</code> (TLS). Generate a 16-character <em>App Password</em> from your Google Security page.</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="text-indigo-400 font-bold">•</span>
                                <span><strong>Custom Domain (Hostinger/cPanel):</strong> Host: <code class="text-indigo-300">smtp.hostinger.com</code> or <code class="text-indigo-300">mail.yourdomain.com</code>, Port: <code class="text-indigo-300">465</code> (SSL) or <code class="text-indigo-300">587</code> (TLS).</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="text-indigo-400 font-bold">•</span>
                                <span><strong>Gym Logo in Emails:</strong> Upload your logo in the <em>Business Info</em> tab. It will automatically appear on all member welcome emails and invoice receipts.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 1: ADD NEW BRANCH                                                   -->
        <!-- ========================================================================= -->
        <div x-show="showAddBranchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div @click.away="showAddBranchModal = false" class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl space-y-0 text-slate-100">
                <div class="p-5 bg-indigo-600/10 border-b border-indigo-500/20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-white">Add New Branch Location</h3>
                            <p class="text-xs text-indigo-300/80">Configure facility branch address and contact details</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddBranchModal = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg cursor-pointer">✕</button>
                </div>

                <form action="{{ route('app.branches.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Branch Name *</label>
                        <input type="text" name="name" required placeholder="e.g. South Extension Branch" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Branch Code</label>
                            <input type="text" name="code" placeholder="e.g. SOUTH01" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs font-mono uppercase focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                            <input type="text" name="phone" placeholder="e.g. +91 98765 43210" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email</label>
                            <input type="email" name="email" placeholder="branch@gym.com" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">City</label>
                            <input type="text" name="city" placeholder="e.g. New Delhi" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">State</label>
                            <input type="text" name="state" placeholder="e.g. Delhi" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Pin / Postal Code</label>
                            <input type="text" name="postal_code" placeholder="e.g. 110049" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Full Street Address</label>
                        <textarea name="address" rows="2" placeholder="e.g. Plot 42, 2nd Floor, Commercial Complex..." 
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" @click="showAddBranchModal = false" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 transition-all cursor-pointer">
                            Save Branch
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 2: EDIT BRANCH                                                      -->
        <!-- ========================================================================= -->
        <div x-show="showEditBranchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div @click.away="showEditBranchModal = false" class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl space-y-0 text-slate-100">
                <div class="p-5 bg-indigo-600/10 border-b border-indigo-500/20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-white">Edit Branch</h3>
                            <p class="text-xs text-indigo-300/80" x-text="'Modify settings for ' + editBranchData.name"></p>
                        </div>
                    </div>
                    <button type="button" @click="showEditBranchModal = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg cursor-pointer">✕</button>
                </div>

                <form :action="'{{ url('/app/branches') }}/' + editBranchData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Branch Name *</label>
                        <input type="text" name="name" required x-model="editBranchData.name" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Branch Code *</label>
                            <input type="text" name="code" required x-model="editBranchData.code" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs font-mono uppercase focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Status *</label>
                            <select name="status" x-model="editBranchData.status" 
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                            <input type="text" name="phone" x-model="editBranchData.phone" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email</label>
                            <input type="email" name="email" x-model="editBranchData.email" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">City</label>
                            <input type="text" name="city" x-model="editBranchData.city" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">State</label>
                            <input type="text" name="state" x-model="editBranchData.state" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Full Street Address</label>
                        <textarea name="address" rows="2" x-model="editBranchData.address" 
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-white block">Primary / Main Gym Branch</span>
                            <span class="text-[11px] text-slate-400">Set this location as the default primary location for your gym</span>
                        </div>
                        <input type="checkbox" name="is_main" value="1" x-model="editBranchData.is_main" 
                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700 cursor-pointer">
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" @click="showEditBranchModal = false" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 transition-all cursor-pointer">
                            Update Branch
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 3: REGISTER BIOMETRIC DEVICE                                        -->
        <!-- ========================================================================= -->
        <div x-show="showAddDeviceModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div @click.away="showAddDeviceModal = false" class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl space-y-0 text-slate-100">
                <div class="p-5 bg-teal-600/10 border-b border-teal-500/20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-white">Register Biometric Device</h3>
                            <p class="text-xs text-teal-300/80">Configure eSSL desktop agent, Hikvision terminal, or ZKTeco</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddDeviceModal = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg cursor-pointer">✕</button>
                </div>

                <form action="{{ route('app.devices.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Device Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Front Reception eSSL Fingerprint" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-teal-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Device Type / Protocol *</label>
                            <select name="type" required 
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none cursor-pointer">
                                <option value="essl_desktop">eSSL Biometric (Desktop Middleware / LAN)</option>
                                <option value="hikvision_facial">Hikvision Face Terminal (ISAPI)</option>
                                <option value="hikvision_turnstile">Hikvision Turnstile Barrier Gate</option>
                                <option value="zkteco_biometric">ZKTeco / Realtime Biometric Push</option>
                                <option value="rfid_reader">RFID Card / NFC Reader</option>
                                <option value="qr_scanner">Dynamic QR Scanner</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Hardware Model</label>
                            <input type="text" name="model" placeholder="e.g. SilkBio-101 / DS-K1T341" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-teal-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Branch Location *</label>
                            <select name="branch_id" required 
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none cursor-pointer">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Access Direction *</label>
                            <select name="direction" required 
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-teal-500 focus:outline-none cursor-pointer">
                                <option value="in">Entry Only (Punch In)</option>
                                <option value="out">Exit Only (Punch Out)</option>
                                <option value="both">Both (Bi-Directional Entry/Exit)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">LAN IP Address (Optional)</label>
                            <input type="text" name="ip_address" placeholder="192.168.1.201" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs font-mono focus:border-teal-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Port</label>
                            <input type="number" name="port" value="80" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs font-mono focus:border-teal-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Machine Serial / Identifier</label>
                        <input type="text" name="serial_number" placeholder="e.g. ESSL-SN-9988123" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs font-mono focus:border-teal-500 focus:outline-none">
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" @click="showAddDeviceModal = false" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold shadow-lg shadow-teal-600/25 transition-all cursor-pointer">
                            Register Device
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
