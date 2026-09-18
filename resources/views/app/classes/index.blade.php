<x-app-layout header="Group Classes & Timetables">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $todayDateFormatted = now()->format('l, d M Y');
        $daysList = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $timeSlots = [
            '07:00' => '7 AM',
            '08:00' => '8 AM',
            '09:00' => '9 AM',
            '10:00' => '10 AM',
            '11:00' => '11 AM',
            '12:00' => '12 PM',
            '13:00' => '1 PM',
            '14:00' => '2 PM',
            '15:00' => '3 PM',
            '16:00' => '4 PM',
            '17:00' => '5 PM',
            '18:00' => '6 PM',
            '19:00' => '7 PM',
            '20:00' => '8 PM',
            '21:00' => '9 PM',
        ];
    @endphp

    <div class="space-y-6" x-data="{
        activeTab: 'today',
        showAddClassModal: false,
        showEditClassModal: false,
        showBookClassModal: false,
        showDeleteClassModal: false,
        classToDelete: { id: null, name: '' },
        classToBook: { schedule_id: null, class_name: '', time: '', day: '' },
        editThumbnailPreview: null,
        addThumbnailPreview: null,
        
        classForm: {
            id: null,
            name: '',
            class_type: 'Yoga',
            instructor_id: '',
            fee: 0,
            validity_days: 30,
            total_sessions: '',
            cancellation_hours: 4,
            sac_code: '',
            gst_rate: '',
            price_includes_gst: false,
            capacity: 20,
            duration_minutes: 60,
            room_location: '',
            status: 'ACTIVE',
            is_featured: false,
            description: '',
            thumbnail_url: null,
            schedules: [
                { day_of_week: 'monday', start_time: '07:00', end_time: '08:00' }
            ]
        },

        addClassScheduleRow() {
            this.classForm.schedules.push({
                day_of_week: 'monday',
                start_time: '07:00',
                end_time: this.calculateEndTime('07:00', this.classForm.duration_minutes)
            });
        },

        removeClassScheduleRow(index) {
            if (this.classForm.schedules.length > 1) {
                this.classForm.schedules.splice(index, 1);
            } else {
                this.classForm.schedules = [{ day_of_week: 'monday', start_time: '', end_time: '' }];
            }
        },

        calculateEndTime(startTime, durationMinutes) {
            if (!startTime) return '';
            const parts = startTime.split(':');
            if (parts.length < 2) return '';
            let hours = parseInt(parts[0], 10);
            let mins = parseInt(parts[1], 10) + parseInt(durationMinutes || 60, 10);
            hours = (hours + Math.floor(mins / 60)) % 24;
            mins = mins % 60;
            return (hours < 10 ? '0' : '') + hours + ':' + (mins < 10 ? '0' : '') + mins;
        },

        onStartTimeChange(index) {
            const sch = this.classForm.schedules[index];
            sch.end_time = this.calculateEndTime(sch.start_time, this.classForm.duration_minutes);
        },

        onDurationChange() {
            this.classForm.schedules.forEach(sch => {
                if (sch.start_time) {
                    sch.end_time = this.calculateEndTime(sch.start_time, this.classForm.duration_minutes);
                }
            });
        },

        previewAddThumbnail(event) {
            const file = event.target.files[0];
            if (file) {
                this.addThumbnailPreview = URL.createObjectURL(file);
            }
        },

        previewEditThumbnail(event) {
            const file = event.target.files[0];
            if (file) {
                this.editThumbnailPreview = URL.createObjectURL(file);
            }
        },

        openAddClass() {
            this.classForm = {
                id: null,
                name: '',
                class_type: 'Yoga',
                instructor_id: '',
                fee: 0,
                validity_days: 30,
                total_sessions: '',
                cancellation_hours: 4,
                sac_code: '',
                gst_rate: '',
                price_includes_gst: false,
                capacity: 20,
                duration_minutes: 60,
                room_location: '',
                status: 'ACTIVE',
                is_featured: false,
                description: '',
                thumbnail_url: null,
                schedules: [
                    { day_of_week: 'monday', start_time: '07:00', end_time: '08:00' }
                ]
            };
            this.addThumbnailPreview = null;
            this.showAddClassModal = true;
        },

        openEditClass(c) {
            let scheds = [];
            if (c.schedules && c.schedules.length > 0) {
                scheds = c.schedules.map(s => ({
                    day_of_week: s.day_of_week,
                    start_time: s.start_time ? s.start_time.substring(0, 5) : '',
                    end_time: s.end_time ? s.end_time.substring(0, 5) : ''
                }));
            } else {
                scheds = [{ day_of_week: 'monday', start_time: '07:00', end_time: '08:00' }];
            }

            this.classForm = {
                id: c.id,
                name: c.name || '',
                class_type: c.class_type || 'Yoga',
                instructor_id: c.instructor_id || '',
                fee: c.fee ? parseFloat(c.fee) : 0,
                validity_days: c.validity_days || 30,
                total_sessions: c.total_sessions || '',
                cancellation_hours: c.cancellation_hours || 4,
                sac_code: c.sac_code || '',
                gst_rate: (c.gst_rate !== null && c.gst_rate !== undefined) ? c.gst_rate : '',
                price_includes_gst: Boolean(c.price_includes_gst),
                capacity: c.capacity || 20,
                duration_minutes: c.duration_minutes || 60,
                room_location: c.room_location || '',
                status: c.status || 'ACTIVE',
                is_featured: Boolean(c.is_featured),
                description: c.description || '',
                thumbnail_url: c.thumbnail_path ? ('/storage/' + c.thumbnail_path) : null,
                schedules: scheds
            };
            this.editThumbnailPreview = null;
            this.showEditClassModal = true;
        },

        confirmDeleteClass(id, name) {
            this.classToDelete = { id: id, name: name };
            this.showDeleteClassModal = true;
        },

        openBookModal(schedule) {
            this.classToBook = {
                schedule_id: schedule.id,
                class_name: schedule.gym_class ? schedule.gym_class.name : 'Fitness Class',
                time: schedule.start_time + ' - ' + schedule.end_time,
                day: schedule.day_of_week
            };
            this.showBookClassModal = true;
        }
    }">

        <!-- ==================== TOP BAR & TABS ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Tab Navigation Pills -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/90 shadow-sm">
                <!-- Today Tab -->
                <button type="button" @click="activeTab = 'today'" :class="activeTab === 'today' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Today</span>
                </button>

                <!-- Timetable Tab -->
                <button type="button" @click="activeTab = 'timetable'" :class="activeTab === 'timetable' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Timetable</span>
                </button>

                <!-- Requests Tab -->
                <button type="button" @click="activeTab = 'requests'" :class="activeTab === 'requests' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Requests</span>
                    @if($bookingRequests->where('status', 'BOOKED')->count() > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-500 text-slate-950 font-black">
                            {{ $bookingRequests->where('status', 'BOOKED')->count() }}
                        </span>
                    @endif
                </button>

                <!-- My Classes Tab -->
                <button type="button" @click="activeTab = 'my_classes'" :class="activeTab === 'my_classes' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>My Classes ({{ $classes->count() }})</span>
                </button>

                <!-- Templates Tab -->
                <button type="button" @click="activeTab = 'templates'" :class="activeTab === 'templates' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                    <span>Templates</span>
                </button>
            </div>

            <!-- + Add Class Button -->
            <button @click="openAddClass()" class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-2 transition-all shadow-lg shadow-indigo-600/25 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Add Class</span>
            </button>
        </div>

        <!-- ==================== TAB 1: TODAY ==================== -->
        <div x-show="activeTab === 'today'" class="space-y-4">
            <!-- Header Row: Date & Class Count -->
            <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 px-1">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $todayDateFormatted }}</span>
                </div>
                <div class="text-slate-500 dark:text-slate-400 text-xs">
                    {{ $todaySchedules->count() }} {{ Str::plural('class', $todaySchedules->count()) }} scheduled
                </div>
            </div>

            @if($todaySchedules->isEmpty())
                <!-- Empty State -->
                <div class="p-16 rounded-3xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-center space-y-3 shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-950 flex items-center justify-center mx-auto text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 1v3M10 1v3M14 1v3"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white">No Classes Today</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Enjoy the break! No classes are scheduled for today.</p>
                    <div class="pt-2">
                        <button @click="openAddClass()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer">
                            + Schedule a Class
                        </button>
                    </div>
                </div>
            @else
                <!-- Today's Classes List Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($todaySchedules as $sch)
                        @php
                            $c = $sch->gymClass;
                            $instructor = $sch->trainer ?? $c?->instructor;
                        @endphp
                        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between space-y-4 shadow-sm">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        @if($c?->thumbnail_path)
                                            <img src="{{ Storage::url($c->thumbnail_path) }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-md">
                                        @else
                                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center border border-indigo-200 dark:border-indigo-500/20">
                                                {{ substr($c?->name ?? 'GC', 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $c?->name }}</h4>
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold mt-0.5 {{ in_array(strtolower($c?->class_type ?? ''), ['yoga', 'pilates']) ? 'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-500/15 dark:text-purple-300 dark:border-purple-500/30' : 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30' }}">
                                                {{ $c?->class_type ?? 'Fitness' }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-slate-100 dark:bg-slate-950 text-indigo-700 dark:text-indigo-300 border border-slate-200 dark:border-slate-800">
                                        {{ $sch->bookings_count ?? 0 }} / {{ $c?->capacity ?? 20 }}
                                    </span>
                                </div>

                                <!-- Class Details -->
                                <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 mb-3">
                                    <div>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Time</span>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ date('h:i A', strtotime($sch->start_time)) }} - {{ date('h:i A', strtotime($sch->end_time)) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Instructor</span>
                                        <span class="font-bold text-slate-900 dark:text-white truncate block">{{ $instructor?->full_name ?? 'Staff Coach' }}</span>
                                    </div>
                                </div>

                                @if($c?->room_location)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 px-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ $c->room_location }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <button type="button" @click="openBookModal({{ Js::from($sch) }})" class="flex-1 py-1.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    <span>Book Member</span>
                                </button>
                                @if($c)
                                    <button type="button" @click="openEditClass({{ Js::from($c) }})" class="p-1.5 rounded-xl bg-slate-100 dark:bg-slate-950 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer" title="Edit Class">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- ==================== TAB 2: TIMETABLE ==================== -->
        <div x-show="activeTab === 'timetable'" class="space-y-4">
            <div class="rounded-3xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/80 text-center">
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-wider w-24 border-r border-slate-200 dark:border-slate-800">TIME</th>
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800">MON</th>
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800">TUE</th>
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800">WED</th>
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800">THU</th>
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800">FRI</th>
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider border-r border-slate-200 dark:border-slate-800">SAT</th>
                                <th class="py-3.5 px-3 text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">SUN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @foreach($timeSlots as $timeKey => $timeLabel)
                                @php
                                    $hourNumber = (int) explode(':', $timeKey)[0];
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors">
                                    <!-- Time Header -->
                                    <td class="py-4 px-3 text-center text-xs font-extrabold text-slate-600 dark:text-slate-400 border-r border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40">
                                        {{ $timeLabel }}
                                    </td>

                                    <!-- 7 Day Columns (Mon to Sun) -->
                                    @foreach($daysList as $dayKey)
                                        @php
                                            $matchingSchedules = $allSchedules->filter(function($s) use ($dayKey, $hourNumber) {
                                                if ($s->day_of_week !== $dayKey) return false;
                                                $sHour = (int) explode(':', $s->start_time)[0];
                                                return $sHour === $hourNumber;
                                            });
                                        @endphp
                                        <td class="py-2 px-2 border-r border-slate-100 dark:border-slate-800/80 align-top min-w-[120px] h-20 last:border-r-0">
                                            @foreach($matchingSchedules as $sch)
                                                @php
                                                    $c = $sch->gymClass;
                                                    $cType = $c?->class_type ?? 'Yoga';
                                                    $isPurple = in_array(strtolower($cType), ['yoga', 'pilates', 'stretch']);
                                                    $isRed = in_array(strtolower($cType), ['hiit', 'crossfit', 'boxing']);
                                                @endphp
                                                <div @click="openEditClass({{ Js::from($c) }})" class="p-2.5 rounded-xl border transition-all cursor-pointer shadow-sm select-none mb-1 {{ $isPurple ? 'border-purple-200 bg-purple-50 text-purple-800 hover:border-purple-300 dark:border-purple-500/40 dark:bg-purple-950/30 dark:text-purple-300 dark:hover:border-purple-400' : ($isRed ? 'border-rose-200 bg-rose-50 text-rose-800 hover:border-rose-300 dark:border-rose-500/40 dark:bg-rose-950/30 dark:text-rose-300 dark:hover:border-rose-400' : 'border-indigo-200 bg-indigo-50 text-indigo-800 hover:border-indigo-300 dark:border-indigo-500/40 dark:bg-indigo-950/30 dark:text-indigo-300 dark:hover:border-indigo-400') }}">
                                                    <div class="font-extrabold text-xs truncate {{ $isPurple ? 'text-purple-800 dark:text-purple-300' : ($isRed ? 'text-rose-800 dark:text-rose-300' : 'text-indigo-800 dark:text-indigo-300') }}">
                                                        {{ $c?->name }}
                                                    </div>
                                                    <div class="text-[10px] opacity-80 mt-0.5">
                                                        {{ date('g:i', strtotime($sch->start_time)) }}-{{ date('g:i A', strtotime($sch->end_time)) }}
                                                    </div>
                                                    <div class="text-[9px] font-black opacity-70 mt-1">
                                                        {{ $sch->bookings->count() }}/{{ $c?->capacity ?? 20 }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 3: REQUESTS ==================== -->
        <div x-show="activeTab === 'requests'" class="space-y-4">
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Member Class Bookings & Requests</h3>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-950 text-indigo-600 dark:text-indigo-400 border border-slate-200 dark:border-slate-800">
                        {{ $bookingRequests->count() }} Records
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-3">Member</th>
                                <th class="py-3 px-3">Class</th>
                                <th class="py-3 px-3">Session Date / Time</th>
                                <th class="py-3 px-3">Instructor</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($bookingRequests as $b)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-950/40 transition-colors">
                                    <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                        {{ $b->member?->full_name }}
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-normal">{{ $b->member?->phone }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $b->classSchedule?->gymClass?->name ?? 'Class' }}</span>
                                        <span class="block text-[10px] text-indigo-600 dark:text-indigo-400">{{ $b->classSchedule?->gymClass?->class_type }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        {{ date('d M Y', strtotime($b->booking_date)) }}
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400">{{ $b->classSchedule ? date('h:i A', strtotime($b->classSchedule->start_time)) : '' }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                                        {{ $b->classSchedule?->trainer?->full_name ?? 'Staff Coach' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($b->status === 'BOOKED')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30">Booked</span>
                                        @elseif($b->status === 'ATTENDED')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30">Attended</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ $b->status }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        @if($b->status === 'BOOKED')
                                            <div class="flex items-center justify-end gap-1.5">
                                                <form action="{{ route('app.classes.booking-status', $b->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="ATTENDED">
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] transition-colors cursor-pointer">
                                                        Mark Attended
                                                    </button>
                                                </form>
                                                <form action="{{ route('app.classes.booking-status', $b->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="CANCELLED">
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-transparent text-[10px] transition-colors cursor-pointer">
                                                        Cancel
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 text-[11px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                                        No booking requests yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 4: MY CLASSES (DIRECTORY LIST) ==================== -->
        <div x-show="activeTab === 'my_classes'" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($classes as $c)
                    <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between space-y-4 shadow-sm">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    @if($c->thumbnail_path)
                                        <img src="{{ Storage::url($c->thumbnail_path) }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-md">
                                    @else
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center border border-indigo-200 dark:border-indigo-500/20">
                                            {{ substr($c->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $c->name }}</h4>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold mt-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30">
                                            {{ $c->class_type }}
                                        </span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $c->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-transparent' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $c->status }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 line-clamp-2">{{ $c->description ?: 'No description provided.' }}</p>

                            <!-- Details Box -->
                            <div class="grid grid-cols-3 gap-1.5 p-2 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 text-center text-xs">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $currency }}{{ number_format($c->fee, 0) }}</div>
                                    <div class="text-[8px] text-slate-500 dark:text-slate-400 font-bold uppercase">Fee</div>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $c->capacity }}</div>
                                    <div class="text-[8px] text-slate-500 dark:text-slate-400 font-bold uppercase">Capacity</div>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $c->duration_minutes }}m</div>
                                    <div class="text-[8px] text-slate-500 dark:text-slate-400 font-bold uppercase">Duration</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button @click="openEditClass({{ Js::from($c) }})" class="flex-1 py-1.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit Class</span>
                            </button>
                            <button @click="confirmDeleteClass({{ $c->id }}, '{{ addslashes($c->name) }}')" class="p-1.5 rounded-xl bg-slate-100 dark:bg-slate-950 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl text-slate-500 dark:text-slate-400 text-xs shadow-sm">
                        No classes created yet. Click "+ Add Class" to create one.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ==================== TAB 5: TEMPLATES ==================== -->
        <div x-show="activeTab === 'templates'" class="space-y-4">
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-4 shadow-sm">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Popular Class Templates</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Quickly spin up popular class templates with predefined duration, capacity, and settings.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-2">
                    <!-- Template 1: Morning Yoga -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-500/40 transition-colors">
                        <div class="font-bold text-slate-900 dark:text-white text-sm mb-1">Morning Yoga Flow</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-3">60 mins • 20 Capacity • Yoga Studio</div>
                        <button @click="openAddClass(); classForm.name='Morning Yoga Flow'; classForm.class_type='Yoga'; classForm.duration_minutes=60; classForm.capacity=20;" class="w-full py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-600 hover:text-white dark:bg-purple-600/20 dark:hover:bg-purple-600 dark:text-purple-300 dark:hover:text-white text-xs font-bold transition-all cursor-pointer border border-purple-200 dark:border-transparent">
                            Use Template
                        </button>
                    </div>

                    <!-- Template 2: HIIT Blast -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-rose-300 dark:hover:border-rose-500/40 transition-colors">
                        <div class="font-bold text-slate-900 dark:text-white text-sm mb-1">HIIT Blast</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-3">45 mins • 20 Capacity • Main Gym Hall</div>
                        <button @click="openAddClass(); classForm.name='HIIT Blast'; classForm.class_type='HIIT'; classForm.duration_minutes=45; classForm.capacity=20;" class="w-full py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-rose-600/20 dark:hover:bg-rose-600 dark:text-rose-300 dark:hover:text-white text-xs font-bold transition-all cursor-pointer border border-rose-200 dark:border-transparent">
                            Use Template
                        </button>
                    </div>

                    <!-- Template 3: CrossFit WOD -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-amber-300 dark:hover:border-amber-500/40 transition-colors">
                        <div class="font-bold text-slate-900 dark:text-white text-sm mb-1">CrossFit WOD</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-3">60 mins • 12 Capacity • Functional Zone</div>
                        <button @click="openAddClass(); classForm.name='CrossFit WOD'; classForm.class_type='CrossFit'; classForm.duration_minutes=60; classForm.capacity=12;" class="w-full py-1.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white dark:bg-amber-600/20 dark:hover:bg-amber-600 dark:text-amber-300 dark:hover:text-white text-xs font-bold transition-all cursor-pointer border border-amber-200 dark:border-transparent">
                            Use Template
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== MODAL: ADD NEW CLASS ==================== -->
        <div x-show="showAddClassModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-5xl w-full max-h-[92vh] overflow-y-auto p-6 sm:p-8 shadow-2xl space-y-6 text-slate-900 dark:text-slate-200" @click.away="showAddClassModal = false">
                
                <!-- Modal Top Header -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Add New Class</h3>
                    <button @click="showAddClassModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.classes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Top Section: Thumbnail + Class Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <!-- Thumbnail Box -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Class Thumbnail</label>
                            <input type="file" id="class_thumb_add" name="thumbnail" accept="image/*" class="hidden" @change="previewAddThumbnail($event)">
                            <label for="class_thumb_add" class="cursor-pointer flex flex-col items-center justify-center w-full h-36 rounded-2xl border-2 border-dashed border-indigo-300 dark:border-indigo-500/40 hover:border-indigo-500 bg-slate-50 dark:bg-slate-950/70 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all text-center p-4 relative overflow-hidden group">
                                <template x-if="addThumbnailPreview">
                                    <img :src="addThumbnailPreview" class="absolute inset-0 w-full h-full object-cover">
                                </template>
                                <template x-if="!addThumbnailPreview">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-8 h-8 text-indigo-500 dark:text-indigo-400 mb-1.5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Click to upload</span>
                                    </div>
                                </template>
                            </label>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Recommended: 400×280px</p>
                        </div>

                        <!-- Name, Type, Instructor -->
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Class Name <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                <input type="text" name="name" x-model="classForm.name" required placeholder="e.g. Morning Yoga Flow" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Class Type <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                    <select name="class_type" x-model="classForm.class_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                        <option value="Yoga">Yoga</option>
                                        <option value="Zumba">Zumba</option>
                                        <option value="HIIT">HIIT</option>
                                        <option value="CrossFit">CrossFit</option>
                                        <option value="Pilates">Pilates</option>
                                        <option value="Boxing">Boxing</option>
                                        <option value="Cycling">Cycling / Spinning</option>
                                        <option value="Strength">Strength & Conditioning</option>
                                        <option value="Aerobics">Aerobics</option>
                                        <option value="Dance">Dance Fitness</option>
                                        <option value="Martial Arts">Martial Arts</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Instructor</label>
                                    <select name="instructor_id" x-model="classForm.instructor_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                        <option value="">Select Instructor (Optional)</option>
                                        @foreach($trainers as $t)
                                            <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->specialization ?? 'Trainer' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Grid: Pricing, Validity, GST, Sessions -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                        <!-- Fee Amount -->
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Fee Amount</label>
                            <input type="number" step="0.01" name="fee" x-model="classForm.fee" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block mt-0.5">Set 0 for free</span>
                        </div>

                        <!-- Validity (days) -->
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Validity (days)</label>
                            <input type="number" name="validity_days" x-model="classForm.validity_days" value="30" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block mt-0.5">Duration in days</span>
                        </div>

                        <!-- Total Sessions -->
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Total Sessions</label>
                            <input type="number" name="total_sessions" x-model="classForm.total_sessions" placeholder="Unlimited" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block mt-0.5">Empty = unlimited</span>
                        </div>

                        <!-- Cancellation Hours -->
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Cancel Hours</label>
                            <input type="number" name="cancellation_hours" x-model="classForm.cancellation_hours" value="4" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block mt-0.5">Hours before</span>
                        </div>

                        <!-- SAC Code -->
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">SAC Code</label>
                            <input type="text" name="sac_code" x-model="classForm.sac_code" placeholder="Optional" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block mt-0.5">Optional</span>
                        </div>

                        <!-- GST Rate (%) -->
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">GST Rate (%)</label>
                            <select name="gst_rate" x-model="classForm.gst_rate" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">No GST / None</option>
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18">18%</option>
                                <option value="28">28%</option>
                            </select>
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block mt-0.5">Optional</span>
                        </div>

                        <!-- GST Inclusive -->
                        <div class="lg:col-span-1 flex flex-col justify-center">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">GST Inclusive</label>
                            <label class="flex items-center gap-1.5 cursor-pointer mt-1">
                                <input type="checkbox" name="price_includes_gst" x-model="classForm.price_includes_gst" value="1" class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-indigo-600 focus:ring-0">
                                <span class="text-[11px] text-slate-700 dark:text-slate-300 font-medium">Inclusive</span>
                            </label>
                        </div>

                        <!-- Max Participants -->
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Capacity</label>
                            <input type="number" name="capacity" x-model="classForm.capacity" value="20" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Row 2: Duration, Room, Status, Featured -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 items-center">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" x-model="classForm.duration_minutes" @input="onDurationChange()" value="60" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Room/Location</label>
                            <input type="text" name="room_location" x-model="classForm.room_location" placeholder="e.g. Studio A" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                            <select name="status" x-model="classForm.status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Featured</label>
                            <label class="flex items-center gap-2 cursor-pointer mt-2">
                                <input type="checkbox" name="is_featured" x-model="classForm.is_featured" value="1" class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-amber-500 focus:ring-0">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="classForm.is_featured ? '★ Featured' : 'Not Featured'"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Description Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <textarea name="description" x-model="classForm.description" rows="2" placeholder="Provide class overview, level requirements, or equipment details..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <!-- Bottom Section: Class Schedule Repeater -->
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white">Class Schedule</h4>
                            </div>
                            <button type="button" @click="addClassScheduleRow()" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1.5 transition-colors border border-slate-200 dark:border-slate-700 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Schedule</span>
                            </button>
                        </div>

                        <!-- Schedule Rows -->
                        <div class="space-y-2">
                            <template x-for="(sch, idx) in classForm.schedules" :key="idx">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800">
                                    <!-- Day Selection -->
                                    <div class="sm:col-span-4">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Day</label>
                                        <select :name="'schedules[' + idx + '][day_of_week]'" x-model="sch.day_of_week" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none capitalize">
                                            <option value="monday">Monday</option>
                                            <option value="tuesday">Tuesday</option>
                                            <option value="wednesday">Wednesday</option>
                                            <option value="thursday">Thursday</option>
                                            <option value="friday">Friday</option>
                                            <option value="saturday">Saturday</option>
                                            <option value="sunday">Sunday</option>
                                        </select>
                                    </div>

                                    <!-- Start Time -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Start Time</label>
                                        <input type="time" :name="'schedules[' + idx + '][start_time]'" x-model="sch.start_time" @input="onStartTimeChange(idx)" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>

                                    <!-- End Time (Auto-calculated) -->
                                    <div class="sm:col-span-4">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">End Time</label>
                                        <input type="time" :name="'schedules[' + idx + '][end_time]'" x-model="sch.end_time" class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>

                                    <!-- Delete Row Button -->
                                    <div class="sm:col-span-1 flex justify-end pt-5">
                                        <button type="button" @click="removeClassScheduleRow(idx)" class="p-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white transition-colors cursor-pointer" title="Remove schedule">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-1">
                            <span>ℹ End time is calculated automatically based on duration.</span>
                        </p>
                    </div>

                    <!-- Modal Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showAddClassModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors border border-slate-200 dark:border-transparent cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs flex items-center gap-2 shadow-lg shadow-indigo-600/25 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Save Class</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: EDIT CLASS ==================== -->
        <div x-show="showEditClassModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-5xl w-full max-h-[92vh] overflow-y-auto p-6 sm:p-8 shadow-2xl space-y-6 text-slate-900 dark:text-slate-200" @click.away="showEditClassModal = false">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Edit Class</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Modify class parameters, pricing, and timetable schedule</p>
                    </div>
                    <button @click="showEditClassModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('/app/classes') }}/' + classForm.id" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="schedules_submitted" value="1">

                    <!-- Top Section: Thumbnail + Class Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Class Thumbnail</label>
                            <input type="file" id="class_thumb_edit" name="thumbnail" accept="image/*" class="hidden" @change="previewEditThumbnail($event)">
                            <label for="class_thumb_edit" class="cursor-pointer flex flex-col items-center justify-center w-full h-36 rounded-2xl border-2 border-dashed border-indigo-300 dark:border-indigo-500/40 hover:border-indigo-500 bg-slate-50 dark:bg-slate-950/70 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all text-center p-4 relative overflow-hidden group">
                                <template x-if="editThumbnailPreview">
                                    <img :src="editThumbnailPreview" class="absolute inset-0 w-full h-full object-cover">
                                </template>
                                <template x-if="!editThumbnailPreview && classForm.thumbnail_url">
                                    <img :src="classForm.thumbnail_url" class="absolute inset-0 w-full h-full object-cover">
                                </template>
                                <template x-if="!editThumbnailPreview && !classForm.thumbnail_url">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-8 h-8 text-indigo-500 dark:text-indigo-400 mb-1.5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Click to change</span>
                                    </div>
                                </template>
                            </label>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Recommended: 400×280px</p>
                        </div>

                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Class Name <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                <input type="text" name="name" x-model="classForm.name" required placeholder="e.g. Morning Yoga Flow" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Class Type <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                    <select name="class_type" x-model="classForm.class_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                        <option value="Yoga">Yoga</option>
                                        <option value="Zumba">Zumba</option>
                                        <option value="HIIT">HIIT</option>
                                        <option value="CrossFit">CrossFit</option>
                                        <option value="Pilates">Pilates</option>
                                        <option value="Boxing">Boxing</option>
                                        <option value="Cycling">Cycling / Spinning</option>
                                        <option value="Strength">Strength & Conditioning</option>
                                        <option value="Aerobics">Aerobics</option>
                                        <option value="Dance">Dance Fitness</option>
                                        <option value="Martial Arts">Martial Arts</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Instructor</label>
                                    <select name="instructor_id" x-model="classForm.instructor_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                        <option value="">Select Instructor (Optional)</option>
                                        @foreach($trainers as $t)
                                            <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->specialization ?? 'Trainer' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Fee Amount</label>
                            <input type="number" step="0.01" name="fee" x-model="classForm.fee" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block mt-0.5">Set 0 for free</span>
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Validity (days)</label>
                            <input type="number" name="validity_days" x-model="classForm.validity_days" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Total Sessions</label>
                            <input type="number" name="total_sessions" x-model="classForm.total_sessions" placeholder="Unlimited" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Cancel Hours</label>
                            <input type="number" name="cancellation_hours" x-model="classForm.cancellation_hours" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">SAC Code</label>
                            <input type="text" name="sac_code" x-model="classForm.sac_code" placeholder="Optional" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">GST Rate (%)</label>
                            <select name="gst_rate" x-model="classForm.gst_rate" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">No GST / None</option>
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18">18%</option>
                                <option value="28">28%</option>
                            </select>
                        </div>
                        <div class="lg:col-span-1 flex flex-col justify-center">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">GST Inclusive</label>
                            <label class="flex items-center gap-1.5 cursor-pointer mt-1">
                                <input type="checkbox" name="price_includes_gst" x-model="classForm.price_includes_gst" value="1" class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-indigo-600 focus:ring-0">
                                <span class="text-[11px] text-slate-700 dark:text-slate-300">Inclusive</span>
                            </label>
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Capacity</label>
                            <input type="number" name="capacity" x-model="classForm.capacity" value="20" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Duration, Room, Status, Featured -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 items-center">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" x-model="classForm.duration_minutes" @input="onDurationChange()" value="60" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Room/Location</label>
                            <input type="text" name="room_location" x-model="classForm.room_location" placeholder="e.g. Studio A" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                            <select name="status" x-model="classForm.status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Featured</label>
                            <label class="flex items-center gap-2 cursor-pointer mt-2">
                                <input type="checkbox" name="is_featured" x-model="classForm.is_featured" value="1" class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-amber-500 focus:ring-0">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="classForm.is_featured ? '★ Featured' : 'Not Featured'"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <textarea name="description" x-model="classForm.description" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <!-- Schedules Repeater -->
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-extrabold text-slate-900 dark:text-white">Class Schedule</h4>
                            <button type="button" @click="addClassScheduleRow()" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1.5 transition-colors border border-slate-200 dark:border-slate-700 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Schedule</span>
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(sch, idx) in classForm.schedules" :key="idx">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800">
                                    <div class="sm:col-span-4">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Day</label>
                                        <select :name="'schedules[' + idx + '][day_of_week]'" x-model="sch.day_of_week" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none capitalize">
                                            <option value="monday">Monday</option>
                                            <option value="tuesday">Tuesday</option>
                                            <option value="wednesday">Wednesday</option>
                                            <option value="thursday">Thursday</option>
                                            <option value="friday">Friday</option>
                                            <option value="saturday">Saturday</option>
                                            <option value="sunday">Sunday</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Start Time</label>
                                        <input type="time" :name="'schedules[' + idx + '][start_time]'" x-model="sch.start_time" @input="onStartTimeChange(idx)" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">End Time</label>
                                        <input type="time" :name="'schedules[' + idx + '][end_time]'" x-model="sch.end_time" class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <div class="sm:col-span-1 flex justify-end pt-5">
                                        <button type="button" @click="removeClassScheduleRow(idx)" class="p-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white transition-colors cursor-pointer" title="Remove">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEditClassModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors border border-slate-200 dark:border-transparent cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs flex items-center gap-2 shadow-lg shadow-indigo-600/25 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Update Class</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: BOOK MEMBER FOR CLASS ==================== -->
        <div x-show="showBookClassModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5 text-slate-900 dark:text-slate-200" @click.away="showBookClassModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Book Class Session</h3>
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5" x-text="classToBook.class_name + ' (' + classToBook.time + ')'"></p>
                    </div>
                    <button @click="showBookClassModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('/app/classes/schedules') }}/' + classToBook.schedule_id + '/book'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Member *</label>
                        <select name="member_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">Select a member...</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Session Date *</label>
                        <input type="date" name="booking_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showBookClassModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs border border-slate-200 dark:border-transparent cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/20 cursor-pointer">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: DELETE CONFIRMATION ==================== -->
        <div x-show="showDeleteClassModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-5 text-center text-slate-900 dark:text-slate-200" @click.away="showDeleteClassModal = false">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/25 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto shadow-lg shadow-rose-500/10">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Delete Fitness Class</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Are you sure you want to delete <span class="font-bold text-slate-900 dark:text-white" x-text="classToDelete.name"></span>? All associated schedule slots and member bookings will also be deleted.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="button" @click="showDeleteClassModal = false" class="flex-1 py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs border border-slate-200 dark:border-transparent cursor-pointer">Cancel</button>
                    <form :action="'{{ url('/app/classes') }}/' + classToDelete.id" method="POST" class="flex-1 inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs shadow-lg shadow-rose-600/25 cursor-pointer">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
