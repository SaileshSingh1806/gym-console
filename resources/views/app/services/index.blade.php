<x-app-layout header="Gym Services & Amenities">
    <div class="space-y-6" x-data="{
        activeTab: 'services',
        showServiceModal: false,
        isEditingService: false,
        serviceFormAction: '{{ route('app.services.store') }}',
        editServiceData: {
            id: '',
            name: '',
            amount: '',
            duration_minutes: 60,
            timeslot_availability: '',
            description: '',
            status: 'active',
            is_visible_in_portal: true,
            is_locker_service: false,
            is_session_countable: false,
            session_count: 1
        },
        showBookingModal: false,
        bookingFormAction: '{{ route('app.services.bookings.store') }}',
        selectedServiceAmount: 0,
        updateAmountFromService(e) {
            const opt = e.target.selectedOptions[0];
            if (opt && opt.dataset.price) {
                this.selectedServiceAmount = opt.dataset.price;
            }
        },
        openAddService() {
            this.isEditingService = false;
            this.serviceFormAction = '{{ route('app.services.store') }}';
            this.editServiceData = {
                id: '',
                name: '',
                amount: '',
                duration_minutes: 60,
                timeslot_availability: '',
                description: '',
                status: 'active',
                is_visible_in_portal: true,
                is_locker_service: false,
                is_session_countable: false,
                session_count: 1
            };
            this.showServiceModal = true;
        },
        openEditService(svc) {
            this.isEditingService = true;
            this.serviceFormAction = '/app/services/' + svc.id;
            this.editServiceData = {
                id: svc.id,
                name: svc.name,
                amount: svc.amount,
                duration_minutes: svc.duration_minutes,
                timeslot_availability: svc.timeslot_availability || '',
                description: svc.description || '',
                status: svc.status || 'active',
                is_visible_in_portal: !!svc.is_visible_in_portal,
                is_locker_service: !!svc.is_locker_service,
                is_session_countable: !!svc.is_session_countable,
                session_count: svc.session_count || 1
            };
            this.showServiceModal = true;
        }
    }">

        <!-- Success / Error Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1">✕</button>
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
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400">
                <div class="font-semibold text-xs mb-1">Please correct the following errors:</div>
                <ul class="list-disc pl-5 text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Top Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 border border-teal-500/20 dark:border-teal-500/30 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.4z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-fg dark:text-white tracking-tight flex items-center gap-2">
                        <span>Services</span>
                    </h2>
                    <p class="text-xs text-fg-muted dark:text-fg-muted">Manage spa, sauna, massages, locker rentals, and add-on gym facilities.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="showBookingModal = true" class="px-4 py-2.5 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg hover:text-fg dark:hover:text-white text-xs font-semibold border border-line dark:border-line transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Book for Member</span>
                </button>

                <button type="button" @click="openAddService()" class="px-4 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center gap-2 transition-all ops-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Service</span>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 p-1.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line max-w-full overflow-x-auto">
            <!-- Services Tab -->
            <button type="button" @click="activeTab = 'services'"
                    :class="activeTab === 'services' ? 'bg-indigo-600 text-white shadow-md font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                <span>Services</span>
                <span class="text-xs px-1.5 py-0.2 rounded-full bg-raised dark:bg-slate-950/40 text-fg-muted dark:text-fg-muted font-mono">{{ $services->count() }}</span>
            </button>

            <!-- Booking Requests Tab -->
            <button type="button" @click="activeTab = 'requests'"
                    :class="activeTab === 'requests' ? 'bg-indigo-600 text-white shadow-md font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/></svg>
                <span>Booking Requests</span>
                @if($bookingRequests->count() > 0)
                    <span class="text-xs px-1.5 py-0.2 rounded-full bg-amber-500 text-slate-950 font-semibold">{{ $bookingRequests->count() }}</span>
                @endif
            </button>

            <!-- All Bookings Tab -->
            <button type="button" @click="activeTab = 'bookings'"
                    :class="activeTab === 'bookings' ? 'bg-indigo-600 text-white shadow-md font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>All Bookings</span>
                <span class="text-xs px-1.5 py-0.2 rounded-full bg-raised dark:bg-slate-950/40 text-fg-muted dark:text-fg-muted font-mono">{{ $allBookings->total() }}</span>
            </button>
        </div>

        <!-- ==================== TAB 1: SERVICES CARDS GRID ==================== -->
        <div x-show="activeTab === 'services'" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($services as $svc)
                    <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line hover:border-line dark:hover:border-line transition-all flex flex-col justify-between group">
                        <div>
                            <!-- Card Header -->
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>

                                <div>
                                    @if($svc->is_visible_in_portal)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                            <span>Visible</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted border border-line dark:border-line">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                            <span>Hidden</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="text-sm font-semibold text-fg dark:text-white tracking-tight leading-tight">{{ $svc->name }}</h3>
                            
                            <div class="mt-1 flex items-baseline gap-1">
                                <span class="text-lg font-semibold text-amber-600 dark:text-amber-400">
                                    {{ auth()->user()->tenant?->currency_symbol ?? '₹' }}{{ number_format($svc->amount, 0) }}
                                </span>
                                <span class="text-xs text-fg-muted dark:text-fg-muted font-normal">
                                    @if($svc->is_session_countable && $svc->session_count > 1)
                                        for {{ $svc->session_count }} sessions
                                    @else
                                        per session
                                    @endif
                                </span>
                            </div>

                            <p class="text-fg-muted dark:text-fg-muted text-xs leading-relaxed mt-2 line-clamp-2 min-h-[28px]">
                                {{ $svc->description ?? 'Premium gym amenity service available for members.' }}
                            </p>

                            <!-- Metadata Box -->
                            <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line my-3 space-y-1.5 text-xs text-fg-muted dark:text-fg-muted">
                                <div class="flex justify-between items-center">
                                    <span class="text-fg-muted dark:text-fg-muted">Duration:</span>
                                    <span class="font-semibold text-fg dark:text-white">{{ $svc->duration_minutes }} mins</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-fg-muted dark:text-fg-muted">Availability:</span>
                                    <span class="font-semibold text-fg dark:text-fg">{{ $svc->timeslot_availability ?? 'By Appointment' }}</span>
                                </div>

                                @if($svc->is_locker_service)
                                    <div class="pt-1 border-t border-line dark:border-line flex items-center justify-between">
                                        <span class="text-fg-muted dark:text-fg-muted">Service Type:</span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20 dark:border-indigo-500/30">
                                            <span>🔒 Locker</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="pt-2.5 border-t border-line dark:border-line flex items-center gap-1.5">
                            <button type="button" @click="openEditService({{ json_encode($svc) }})"
                                    class="flex-1 py-1.5 px-2.5 rounded-lg bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg hover:text-fg dark:hover:text-white text-xs font-semibold flex items-center justify-center gap-1 transition-colors">
                                <svg class="w-3.5 h-3.5 text-fg-muted dark:text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Edit</span>
                            </button>

                            <form action="{{ route('app.services.toggle-visibility', $svc->id) }}" method="POST" class="inline">
                                @csrf
                                @if($svc->is_visible_in_portal)
                                    <button type="submit" class="py-1.5 px-2.5 rounded-lg bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 text-xs font-semibold border border-line dark:border-line transition-colors" title="Hide from Member Portal">
                                        <span>Hide</span>
                                    </button>
                                @else
                                    <button type="submit" class="py-1.5 px-2.5 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/15 hover:bg-emerald-500/20 dark:hover:bg-emerald-500/25 text-emerald-600 dark:text-emerald-400 text-xs font-semibold border border-emerald-500/20 dark:border-emerald-500/30 transition-colors" title="Show in Member Portal">
                                        <span>Show</span>
                                    </button>
                                @endif
                            </form>

                            <form action="{{ route('app.services.delete', $svc->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to delete the gym service '{{ addslashes($svc->name) }}'?" 
                                  data-confirm-title="Delete Gym Service" 
                                  data-confirm-btn="Yes, Delete Service" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-600 dark:text-rose-400 text-xs font-semibold transition-colors cursor-pointer" title="Delete Service">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-12 text-center bg-surface dark:bg-surface border border-dashed border-line dark:border-line rounded-xl">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-fg dark:text-white mb-1">No Services Added Yet</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted max-w-sm mx-auto mb-4">Create your first gym service (Massages, Sauna, Locker rentals, etc.) to offer to members.</p>
                        <button type="button" @click="openAddService()" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold ops-primary">
                            + Add New Service
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ==================== TAB 2: BOOKING REQUESTS ==================== -->
        <div x-show="activeTab === 'requests'" class="space-y-4">
            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <div class="p-4 bg-raised dark:bg-slate-950/60 border-b border-line dark:border-line flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-semibold text-fg dark:text-white">Pending Member Booking Requests</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Review, approve, assign lockers, and confirm service reservations.</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-semibold border border-amber-500/20 dark:border-amber-500/30">
                        {{ $bookingRequests->count() }} Pending
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[650px]">
                        <thead class="bg-raised dark:bg-surface border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
                            <tr>
                                <th class="py-3.5 px-4 font-semibold">Member</th>
                                <th class="py-3.5 px-4 font-semibold">Requested Service</th>
                                <th class="py-3.5 px-4 font-semibold">Date & Time</th>
                                <th class="py-3.5 px-4 font-semibold">Fee (₹)</th>
                                <th class="py-3.5 px-4 font-semibold">Locker Number</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                            @forelse($bookingRequests as $req)
                                <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-fg dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-raised dark:bg-raised text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold text-xs">
                                                {{ substr($req->member?->first_name ?? 'M', 0, 1) }}
                                            </div>
                                            <span>{{ $req->member?->full_name ?? 'Member #' . $req->member_id }}</span>
                                            @if($req->member?->member_code)
                                                <span class="text-xs text-fg-muted dark:text-fg-muted font-mono">- {{ $req->member->member_code }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-teal-600 dark:text-teal-400">
                                        {{ $req->service?->name ?? 'Service #' . $req->gym_service_id }}
                                    </td>
                                    <td class="py-3.5 px-4 text-fg-muted dark:text-fg-muted">
                                        {{ $req->booking_date?->format('d M Y') ?? '-' }}
                                        @if($req->booking_time)
                                            <span class="text-xs text-fg-muted dark:text-fg-muted block">{{ date('h:i A', strtotime($req->booking_time)) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-fg dark:text-white">
                                        ₹{{ number_format($req->amount_paid ?? 0, 0) }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($req->service?->is_locker_service)
                                            <form action="{{ route('app.services.bookings.status', $req->id) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                <input type="text" name="locker_number" value="{{ $req->locker_number }}" placeholder="e.g. L-102"
                                                       class="w-24 px-2 py-1 rounded bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white">
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs">Approve</button>
                                            </form>
                                        @else
                                            <span class="text-fg-muted dark:text-fg-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if(!($req->service?->is_locker_service))
                                                <form action="{{ route('app.services.bookings.status', $req->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1">
                                                        <span>Approve</span>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('app.services.bookings.status', $req->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-raised dark:bg-raised hover:bg-rose-50 dark:hover:bg-rose-950/60 text-fg-muted dark:text-fg-muted hover:text-rose-600 dark:hover:text-rose-400 font-semibold text-xs border border-line dark:border-line">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-fg-muted dark:text-fg-muted text-xs">
                                        No pending booking requests.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 3: ALL BOOKINGS ==================== -->
        <div x-show="activeTab === 'bookings'" class="space-y-4">
            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <div class="p-4 bg-raised dark:bg-slate-950/60 border-b border-line dark:border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-xs font-semibold text-fg dark:text-white">All Member Service Bookings</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Track active sessions, deduction history, and locker allocations.</p>
                    </div>

                    <button type="button" @click="showBookingModal = true" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center gap-1.5 self-start sm:self-auto ops-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Book Service</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[650px]">
                        <thead class="bg-raised dark:bg-surface border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
                            <tr>
                                <th class="py-4 px-4 font-semibold">MEMBER</th>
                                <th class="py-4 px-4 font-semibold">SERVICE</th>
                                <th class="py-4 px-4 font-semibold">AMOUNT</th>
                                <th class="py-4 px-4 font-semibold">SESSIONS</th>
                                <th class="py-4 px-4 font-semibold">DATES</th>
                                <th class="py-4 px-4 font-semibold">LOCKER</th>
                                <th class="py-4 px-4 font-semibold">STATUS</th>
                                <th class="py-4 px-4 font-semibold text-right">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                            @forelse($allBookings as $bk)
                                <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-fg dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $bk->member?->full_name ?? 'Member #' . $bk->member_id }}</span>
                                            @if($bk->member?->member_code)
                                                <span class="text-xs text-fg-muted dark:text-fg-muted font-mono">- {{ $bk->member->member_code }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-fg dark:text-fg">
                                        {{ $bk->service?->name ?? 'Service #' . $bk->gym_service_id }}
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-fg dark:text-white">
                                        {{ number_format($bk->amount_paid ?? 0, 0) }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($bk->service?->is_session_countable)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $bk->sessions_left > 0 ? 'bg-teal-500/10 dark:bg-teal-500/20 text-teal-700 dark:text-teal-400 border border-teal-500/20 dark:border-teal-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $bk->sessions_left > 0 ? 'bg-teal-500 dark:bg-teal-400' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                                                <span>{{ $bk->sessions_left }}/{{ $bk->total_sessions }} left</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500/10 dark:bg-teal-500/20 text-teal-700 dark:text-teal-400 border border-teal-500/20 dark:border-teal-500/30">
                                                <span>{{ $bk->sessions_left }}/{{ $bk->total_sessions }} left</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-fg-muted dark:text-fg-muted font-medium">
                                        {{ $bk->booking_date?->format('d M') ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($bk->locker_number)
                                            <span class="px-2 py-0.5 rounded bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-mono font-semibold text-xs border border-indigo-500/20 dark:border-indigo-500/30">
                                                {{ $bk->locker_number }}
                                            </span>
                                        @else
                                            <span class="text-fg-muted dark:text-fg-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($bk->status === 'active')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30">
                                                Active
                                            </span>
                                        @elseif($bk->status === 'completed')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted">
                                                Completed
                                            </span>
                                        @elseif($bk->status === 'pending')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/20 dark:border-amber-500/30">
                                                Pending
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400">
                                                {{ ucfirst($bk->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($bk->status === 'active' && $bk->sessions_left > 0)
                                                <form action="{{ route('app.services.bookings.deduct', $bk->id) }}" method="POST" 
                                                      data-confirm="Deduct 1 used session for {{ addslashes($bk->member?->full_name ?? 'this member') }}?" 
                                                      data-confirm-title="Deduct Service Session" 
                                                      data-confirm-btn="Yes, Deduct Session" 
                                                      data-confirm-type="primary" 
                                                      class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center gap-1 transition-all cursor-pointer ops-primary" title="Deduct 1 session">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Deduct</span>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('app.services.bookings.delete', $bk->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to delete this booking record?" 
                                                  data-confirm-title="Delete Booking Record" 
                                                  data-confirm-btn="Yes, Delete Booking" 
                                                  data-confirm-type="danger" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded-lg text-fg-muted hover:text-rose-600 dark:text-fg-muted dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Delete Booking">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-12 text-center text-fg-muted dark:text-fg-muted text-xs">
                                        No member service bookings found. Click "Book Service" to record a booking.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($allBookings->hasPages())
                    <div class="p-4 border-t border-line dark:border-line">
                        {{ $allBookings->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- ==================== MODAL: ADD / EDIT SERVICE ==================== -->
        <div x-show="showServiceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl w-full max-w-2xl overflow-hidden relative my-auto max-h-[90vh] flex flex-col text-fg dark:text-fg" @click.away="showServiceModal = false">
                <form :action="serviceFormAction" method="POST" class="flex flex-col max-h-[90vh]">
                    @csrf
                    <div class="p-4 sm:p-5 border-b border-line dark:border-line flex items-center justify-between shrink-0 bg-surface border">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white" x-text="isEditingService ? 'Edit Service' : 'Add New Service'">Add New Service</h3>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Configure service pricing, duration, sessions, and member portal availability.</p>
                            </div>
                        </div>
                        <button type="button" @click="showServiceModal = false" class="w-8 h-8 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Service Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="editServiceData.name" required placeholder="e.g. Steam Bath, Sauna, Massage"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:outline-none focus:border-accent transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
                                <input type="number" name="amount" x-model="editServiceData.amount" required placeholder="0" min="0" step="1"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs font-semibold focus:outline-none focus:border-accent transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Duration (Minutes)</label>
                                <input type="number" name="duration_minutes" x-model="editServiceData.duration_minutes" placeholder="60" min="0"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:outline-none focus:border-accent transition-colors">
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">How long does this service typically take?</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Timeslot / Availability</label>
                                <input type="text" name="timeslot_availability" x-model="editServiceData.timeslot_availability" placeholder="e.g. 9 AM - 6 PM, Morning Only, By Appointment"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:outline-none focus:border-accent transition-colors">
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">When is this service available?</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Description</label>
                            <textarea name="description" x-model="editServiceData.description" rows="3" placeholder="Describe the service, what's included, any requirements..."
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:outline-none focus:border-accent leading-relaxed"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Status</label>
                            <select name="status" x-model="editServiceData.status"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:outline-none focus:border-accent">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="space-y-3 pt-2 border-t border-line dark:border-line">
                            <!-- Show in Member Panel -->
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line hover:border-line dark:hover:border-line transition-colors">
                                <input type="checkbox" name="is_visible_in_portal" value="1" x-model="editServiceData.is_visible_in_portal"
                                       class="mt-0.5 rounded border-line dark:border-line bg-surface dark:bg-surface text-indigo-600 focus:ring-0">
                                <div>
                                    <span class="text-xs font-semibold text-fg dark:text-white block">Show in Member Panel</span>
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">When enabled, members can see and request this service in their portal.</span>
                                </div>
                            </label>

                            <!-- Locker Service -->
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line hover:border-line dark:hover:border-line transition-colors">
                                <input type="checkbox" name="is_locker_service" value="1" x-model="editServiceData.is_locker_service"
                                       class="mt-0.5 rounded border-line dark:border-line bg-surface dark:bg-surface text-indigo-600 focus:ring-0">
                                <div>
                                    <span class="text-xs font-semibold text-fg dark:text-white block">Locker Service</span>
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">When enabled, admin must assign a locker number when approving bookings.</span>
                                </div>
                            </label>

                            <!-- Session Countable -->
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line hover:border-line dark:hover:border-line transition-colors">
                                <input type="checkbox" name="is_session_countable" value="1" x-model="editServiceData.is_session_countable"
                                       class="mt-0.5 rounded border-line dark:border-line bg-surface dark:bg-surface text-indigo-600 focus:ring-0">
                                <div>
                                    <span class="text-xs font-semibold text-fg dark:text-white block">Session Countable Pack</span>
                                    <span class="text-xs text-fg-muted dark:text-fg-muted">The price buys a fixed number of sessions. Each visit is deducted until the pack runs out.</span>
                                </div>
                            </label>

                            <div x-show="editServiceData.is_session_countable" class="pl-8 pt-1">
                                <label class="block text-xs font-semibold text-indigo-600 dark:text-indigo-400 mb-1">Total Sessions Included in Pack</label>
                                <input type="number" name="session_count" x-model="editServiceData.session_count" min="1" max="500" placeholder="e.g. 2, 5, 10"
                                       class="w-40 px-3 py-1.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:outline-none focus:border-accent">
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 bg-raised dark:bg-surface border-t border-line dark:border-line flex items-center justify-between gap-3 shrink-0">
                        <button type="button" @click="showServiceModal = false" class="px-4 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-fg text-xs font-semibold border border-line dark:border-line transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer ops-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="isEditingService ? 'Update Service' : 'Add Service'">Add Service</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: BOOK SERVICE FOR MEMBER ==================== -->
        <div x-show="showBookingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl w-full max-w-lg overflow-hidden relative my-auto max-h-[90vh] flex flex-col text-fg dark:text-fg" @click.away="showBookingModal = false">
                <form action="{{ route('app.services.bookings.store') }}" method="POST" class="flex flex-col max-h-[90vh]">
                    @csrf
                    <div class="p-4 sm:p-5 border-b border-line dark:border-line flex items-center justify-between shrink-0 bg-surface border">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-fg dark:text-white">Book Service for Member</h3>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Enroll a member into an add-on gym service or assign a locker.</p>
                            </div>
                        </div>
                        <button type="button" @click="showBookingModal = false" class="w-8 h-8 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Select Member <span class="text-rose-500">*</span></label>
                            <select name="member_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:outline-none focus:border-accent">
                                <option value="">-- Choose Member --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone ?? 'No phone' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Select Service <span class="text-rose-500">*</span></label>
                            <select name="gym_service_id" required @change="updateAmountFromService($event)" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:outline-none focus:border-accent">
                                <option value="">-- Choose Service --</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" data-price="{{ $s->amount }}">
                                        {{ $s->name }} (₹{{ number_format($s->amount, 0) }} - {{ $s->is_session_countable ? $s->session_count . ' sessions' : 'single' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Booking Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="booking_date" required value="{{ date('Y-m-d') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:outline-none focus:border-accent">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Time (Optional)</label>
                                <input type="time" name="booking_time"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:outline-none focus:border-accent">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Amount Paid (₹)</label>
                                <input type="number" name="amount_paid" :value="selectedServiceAmount" min="0" step="1"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:outline-none focus:border-accent">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Locker Number (If applicable)</label>
                                <input type="text" name="locker_number" placeholder="e.g. L-104"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:outline-none focus:border-accent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Notes / Special Requests</label>
                            <textarea name="notes" rows="2" placeholder="Optional notes about this booking..."
                                      class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:outline-none focus:border-accent"></textarea>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 bg-raised dark:bg-surface border-t border-line dark:border-line flex items-center justify-between gap-3 shrink-0">
                        <button type="button" @click="showBookingModal = false" class="px-4 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-fg text-xs font-semibold border border-line dark:border-line transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all cursor-pointer ops-primary">
                            Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
