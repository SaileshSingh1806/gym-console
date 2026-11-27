@php
    $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $membersMap = $members->getCollection()->mapWithKeys(function ($m) {
            $meta = is_array($m->metadata) ? $m->metadata : [];
            $membership = $m->activeMembership ?? $m->latestMembership;
            return [(string) $m->id => [
                'id' => $m->id,
                'member_code' => (string) ($m->member_code ?? ''),
                'join_date' => $m->join_date ? $m->join_date->format('Y-m-d') : '',
                'first_name' => (string) ($m->first_name ?? ''),
                'last_name' => (string) ($m->last_name ?? ''),
                'phone' => (string) ($m->phone ?? ''),
                'alternate_phone' => (string) ($meta['alternate_phone'] ?? ''),
                'email' => (string) ($m->email ?? ''),
                'gender' => (string) ($m->gender ?? 'male'),
                'dob' => $m->dob ? $m->dob->format('Y-m-d') : '',
                'blood_group' => (string) ($meta['blood_group'] ?? ''),
                'city' => (string) ($meta['city'] ?? ''),
                'address' => (string) ($m->address ?? ''),
                'height' => (string) ($meta['height'] ?? ''),
                'height_unit' => (string) ($meta['height_unit'] ?? 'cm'),
                'weight' => (string) ($meta['weight'] ?? ''),
                'target_weight' => (string) ($meta['target_weight'] ?? ''),
                'fitness_goal' => (string) ($meta['fitness_goal'] ?? ''),
                'medical_history' => (string) ($meta['medical_history'] ?? ''),
                'emergency_contact_name' => (string) ($m->emergency_contact_name ?? ''),
                'emergency_contact_phone' => (string) ($m->emergency_contact_phone ?? ''),
                'emergency_relation' => (string) ($meta['emergency_relation'] ?? ''),
                'branch_id' => $m->branch_id ?? '',
                'status' => (string) ($m->status ?? 'ACTIVE'),
                'membership_plan_id' => $membership?->membership_plan_id ?? '',
                'membership_start_date' => $membership?->start_date ? $membership->start_date->format('Y-m-d') : '',
                'membership_end_date' => $membership?->end_date ? $membership->end_date->format('Y-m-d') : '',
                'notes' => (string) ($m->notes ?? ''),
                'photo_path' => (string) ($m->photo_path ?? ''),
                'membership' => $membership ? [
                    'final_amount' => (float) ($membership->final_amount ?? 0),
                    'paid_amount' => (float) ($membership->paid_amount ?? 0),
                    'plan_name' => $membership->plan?->name ?? 'Active Membership',
                ] : null,
            ]];
        });

@endphp
        <!-- Member cards and table adapt to available content width, including the sidebar. -->

        <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
            
            <!-- Mobile Cards View -->
            <div class="members-cards divide-y divide-line dark:divide-line">
                @forelse($members as $member)
                    @php
                        $membership = $member->activeMembership ?? $member->latestMembership;
                        $finalAmt = (float) ($membership?->final_amount ?? 0);
                        $paidAmt = (float) ($membership?->paid_amount ?? 0);
                        $remainingAmt = max(0, $finalAmt - $paidAmt);
                        $daysLeft = $membership?->end_date ? (int) now()->startOfDay()->diffInDays($membership->end_date->copy()->startOfDay(), false) : null;
                        $statusBadge = match($member->status) {
                            'ACTIVE' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                            'SUSPENDED' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
                            'EXPIRED' => 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20',
                            default => 'bg-slate-100 dark:bg-slate-500/10 text-slate-700 dark:text-slate-400 border-slate-200 dark:border-slate-500/20',
                        };
                    @endphp
                    <div class="p-4 space-y-3">
                        <!-- Top Row: Member Header + Status -->
                        <div class="flex items-start justify-between gap-2">
                            <a href="{{ route('app.members.show', $member->id) }}" class="flex items-center gap-2.5 min-w-0 group">
                                @if($member->avatar_url)
                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->full_name }}" loading="lazy" class="w-10 h-10 rounded-xl object-cover border border-amber-200 dark:border-amber-500/30 shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 flex items-center justify-center font-semibold text-amber-600 dark:text-amber-400 text-sm shrink-0">
                                        {{ strtoupper(substr($member->first_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-semibold text-fg dark:text-white text-sm truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                        {{ $member->full_name }}
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-mono font-semibold">{{ $member->member_code }}</span>
                                        <span class="text-fg-muted dark:text-fg-muted">•</span>
                                        <span class="text-xs text-fg-muted dark:text-fg-muted">{{ $member->branch?->name ?? 'Main Gym' }}</span>
                                    </div>
                                </div>
                            </a>

                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $statusBadge }} shrink-0">
                                {{ $member->status }}
                            </span>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line text-xs">
                            <!-- Contact -->
                            <div class="space-y-0.5">
                                <span class="text-xs text-fg-muted dark:text-fg-muted block font-medium">Contact</span>
                                <div class="flex items-center gap-1.5">
                                    <a href="tel:{{ $member->phone }}" class="font-semibold text-fg dark:text-white hover:text-amber-600 dark:hover:text-amber-400">
                                        {{ $member->phone }}
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank" title="WhatsApp" class="text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Plan & Expiry -->
                            <div class="space-y-0.5">
                                <span class="text-xs text-fg-muted dark:text-fg-muted block font-medium">Plan</span>
                                @if($membership)
                                    <div class="font-semibold text-fg dark:text-white break-words">
                                        {{ $membership->plan?->name ?? 'Active Plan' }}
                                    </div>
                                    <div class="text-[10px] {{ $daysLeft !== null && $daysLeft < 7 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                                        @if($daysLeft !== null && $daysLeft >= 0)
                                            {{ $daysLeft }}d left
                                        @elseif($daysLeft !== null && $daysLeft < 0)
                                            Expired
                                        @else
                                            No end date
                                        @endif
                                    </div>
                                @else
                                    <span class="text-fg-muted text-xs italic">No active plan</span>
                                @endif
                            </div>

                            <!-- Fee balance: separate payment status from membership status -->
                            <div class="col-span-2 pt-2 border-t border-line space-y-2">
                                <span class="text-xs text-fg-muted font-medium">Fee balance</span>
                                @if($membership && $membership->status === 'CANCELLED')
                                    <div class="text-xs text-fg-muted">Cancelled / Transferred</div>
                                @elseif($membership)
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="min-w-0">
                                            <div class="text-fg-muted">Total fee</div>
                                            <div class="font-semibold text-fg tabular-nums break-words">{{ $currency }}{{ number_format($finalAmt, 2) }}</div>
                                        </div>
                                        <div class="min-w-0 text-right">
                                            <div class="text-fg-muted">Paid amount</div>
                                            <div class="font-semibold text-emerald-700 dark:text-emerald-400 tabular-nums break-words">{{ $currency }}{{ number_format($paidAmt, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg border {{ $remainingAmt > 0 ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-500/10 dark:border-rose-500/30 dark:text-rose-400' : 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' }}">
                                        <span class="font-semibold">{{ $remainingAmt > 0 ? 'Payment due' : 'Paid in full' }}</span>
                                        <span class="text-base font-bold tabular-nums text-right break-words">{{ $currency }}{{ number_format($remainingAmt, 2) }}</span>
                                    </div>
                                @else
                                    <span class="text-fg-muted text-xs block">No membership</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions Bar -->
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            @if($membership && $remainingAmt > 0)
                                <button type="button" @click="openCollectFeeModal({{ $member->id }})" class="grow basis-full min-w-0 min-h-10 py-2 px-3 rounded-lg bg-accent text-accent-on ops-primary font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Collect {{ $currency }}{{ number_format($remainingAmt, 2) }}</span>
                                </button>
                            @endif

                            <a href="{{ route('app.members.show', $member->id) }}" class="{{ ($membership && $remainingAmt > 0) ? 'px-3' : 'flex-1' }} min-h-10 py-2 rounded-lg bg-surface text-fg border border-line hover:bg-raised font-semibold text-xs flex items-center justify-center gap-1.5 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Profile</span>
                            </a>

                            <button type="button" @click="openEditModal({{ $member->id }})" class="p-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line transition-colors cursor-pointer" title="Edit Member">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            <form action="{{ route('app.members.delete', $member->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to remove member '{{ addslashes($member->full_name) }}'?" 
                                  data-confirm-title="Remove Gym Member" 
                                  data-confirm-btn="Yes, Delete" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 transition-colors cursor-pointer" title="Delete Member">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-fg-muted dark:text-fg-muted space-y-3">
                        <p class="text-sm">No gym members found matching your search or filters.</p>
                        <a href="{{ route('app.members.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-semibold text-xs">Enroll First Member</a>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="members-desktop overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[960px] table-auto">
                    <thead class="bg-raised dark:bg-slate-950/70 border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
                        <tr>
                            <th class="py-3 px-3.5 font-semibold">Member</th>
                            <th class="py-3 px-3.5 font-semibold">Contact</th>
                            <th class="py-3 px-3.5 font-semibold">Branch</th>
                            <th class="py-3 px-3.5 font-semibold">Plan & Validity</th>
                            <th class="py-3 px-3.5 font-semibold">Fee & Balance</th>
                            <th class="py-3 px-2.5 font-semibold text-center">Status</th>
                            <th class="py-3 px-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                        @forelse($members as $member)
                            @php
                                $membership = $member->activeMembership ?? $member->latestMembership;
                                $finalAmt = (float) ($membership?->final_amount ?? 0);
                                $paidAmt = (float) ($membership?->paid_amount ?? 0);
                                $remainingAmt = max(0, $finalAmt - $paidAmt);
                                $daysLeft = $membership?->end_date ? (int) now()->startOfDay()->diffInDays($membership->end_date->copy()->startOfDay(), false) : null;
                            @endphp
                            <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                <!-- Member info with ID and Joined cleanly stacked -->
                                <td class="py-3 px-3.5">
                                    <a href="{{ route('app.members.show', $member->id) }}" class="flex items-center gap-2.5 group">
                                        @if($member->avatar_url)
                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->full_name }}" loading="lazy" class="w-8 h-8 rounded-lg object-cover border border-amber-200 dark:border-amber-500/30 shrink-0 group-hover:scale-105 transition-transform">
                                        @else
                                            <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 dark:border-amber-500/30 flex items-center justify-center font-semibold text-amber-600 dark:text-amber-400 text-xs shrink-0 group-hover:scale-105 transition-transform">
                                                {{ strtoupper(substr($member->first_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <span class="font-semibold text-fg dark:text-white text-xs block leading-tight truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $member->full_name }}</span>
                                            <span class="text-xs text-amber-600 dark:text-amber-400 font-mono font-medium block leading-tight mt-0.5">{{ $member->member_code }}</span>
                                            <span class="text-xs text-fg-muted dark:text-fg-muted block leading-tight mt-0.5">Joined: {{ $member->join_date ? $member->join_date->format('d M Y') : 'N/A' }}</span>
                                        </div>
                                    </a>
                                </td>

                                <!-- Contact -->
                                <td class="py-3 px-3.5">
                                    <div class="font-medium text-fg dark:text-white flex items-center gap-1.5 leading-tight text-xs">
                                        <span>{{ $member->phone }}</span>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank" title="Send WhatsApp Message" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </a>
                                    </div>
                                    <div class="text-xs text-fg-muted dark:text-fg-muted leading-tight mt-0.5 truncate max-w-[150px]">{{ $member->email ?? 'No email' }}</div>
                                </td>

                                <!-- Branch -->
                                <td class="py-3 px-3 text-fg-muted dark:text-fg-muted">
                                    <span class="truncate block max-w-[120px]">{{ $member->branch?->name ?? 'Main Gym' }}</span>
                                </td>

                                <!-- Plan & Validity -->
                                <td class="py-3 px-3.5">
                                    @if($membership && $membership->status === 'CANCELLED')
                                        <div class="font-semibold text-fg-muted dark:text-fg-muted leading-tight flex flex-wrap items-center gap-1.5">
                                            <span class="line-through">{{ $membership->plan?->name ?? 'Custom Plan' }}</span>
                                            <span class="px-1.5 py-0.2 rounded bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 text-xs font-semibold border border-rose-200 dark:border-rose-500/20">Cancelled</span>
                                        </div>
                                        <div class="text-xs text-fg-muted dark:text-fg-muted leading-tight mt-0.5 break-words">
                                            Package Cancelled / Transferred
                                        </div>
                                    @elseif($membership)
                                        <div class="font-semibold text-fg dark:text-white leading-tight flex flex-wrap items-center gap-1.5">
                                            <span>{{ $membership->plan?->name ?? 'Custom Plan' }}</span>
                                            @if($daysLeft !== null)
                                                @if($daysLeft > 7)
                                                    <span class="px-1.5 py-0.2 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-semibold border border-emerald-200 dark:border-emerald-500/20">{{ $daysLeft }}d left</span>
                                                @elseif($daysLeft >= 0)
                                                    <span class="px-1.5 py-0.2 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-xs font-semibold border border-amber-200 dark:border-amber-500/20">{{ $daysLeft }}d left</span>
                                                @else
                                                    <span class="px-1.5 py-0.2 rounded bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 text-xs font-semibold border border-rose-200 dark:border-rose-500/20">Expired</span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-xs text-fg-muted dark:text-fg-muted leading-tight mt-0.5 font-mono">
                                            {{ $membership->start_date ? $membership->start_date->format('d M') : '—' }} to {{ $membership->end_date ? $membership->end_date->format('d M Y') : '—' }}
                                        </div>
                                    @else
                                        <span class="text-fg-muted italic text-xs">No active package</span>
                                    @endif
                                </td>

                                <!-- Fee & Balance -->
                                <td class="py-3 px-3.5">
                                    @if($membership && $membership->status === 'CANCELLED')
                                        <div class="text-xs text-fg-muted">Cancelled / Transferred</div>
                                    @elseif($membership)
                                        <div class="text-xs text-fg-muted tabular-nums whitespace-nowrap">Total: <span class="text-fg font-medium">{{ $currency }}{{ number_format($finalAmt, 2) }}</span></div>
                                        <div class="text-xs text-fg-muted tabular-nums whitespace-nowrap mt-1">Paid: <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ $currency }}{{ number_format($paidAmt, 2) }}</span></div>
                                        <div class="inline-flex items-center gap-1.5 rounded-md border px-2 py-1 mt-1.5 text-xs font-semibold tabular-nums whitespace-nowrap {{ $remainingAmt > 0 ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30' : 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' }}">
                                            {{ $remainingAmt > 0 ? 'Due: ' : 'Paid in full · ' }}{{ $currency }}{{ number_format($remainingAmt, 2) }}
                                        </div>
                                    @else
                                        <span class="text-fg-muted text-xs">No membership</span>
                                    @endif
                                    @if($membership && $remainingAmt > 0)
                                            <button type="button" @click="openCollectFeeModal({{ $member->id }})" title="Collect Remaining Fees" class="py-1.5 px-2.5 rounded-lg bg-accent text-accent-on ops-primary transition-all flex items-center gap-1 text-xs font-semibold cursor-pointer mt-2 whitespace-nowrap">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                <span>Collect payment</span>
                                            </button>
                                        @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-2.5 text-center">
                                    @php
                                        $statusBadge = match($member->status) {
                                            'ACTIVE' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                                            'SUSPENDED' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
                                            'EXPIRED' => 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20',
                                            default => 'bg-slate-100 dark:bg-slate-500/10 text-slate-700 dark:text-slate-400 border-slate-200 dark:border-slate-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold border {{ $statusBadge }} inline-block">
                                        {{ $member->status }}
                                    </span>
                                </td>

                                <!-- Actions Column -->
                                <td class="py-3 px-3.5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5 min-w-[84px] max-w-[112px] ml-auto">
                                        

                                        @php
                                            $firstPayment = $membership?->payments?->first();
                                        @endphp
                                        @if($firstPayment)
                                            <a href="{{ route('app.invoices.show', $firstPayment->id) }}" target="_blank" title="View / Print Tax Invoice ({{ $firstPayment->receipt_number }})" class="p-1.5 rounded-lg bg-raised hover:bg-amber-50 text-fg-muted hover:text-amber-600 border border-line dark:bg-raised dark:hover:bg-amber-500/20 dark:text-fg-muted dark:hover:text-amber-400 dark:border-line transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                        @endif

                                        <a href="{{ route('app.members.show', $member->id) }}" title="View Member Profile" class="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg bg-surface hover:bg-raised text-fg border border-line transition-colors text-xs font-medium whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Profile</span>
                                        </a>

                                        <button type="button" @click="openEditModal({{ $member->id }})" title="Edit Member" class="p-1.5 rounded-lg bg-raised hover:bg-slate-200 text-fg-muted hover:text-fg border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-white dark:border-line transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form action="{{ route('app.members.delete', $member->id) }}" method="POST" 
                                              data-confirm="Are you sure you want to remove member '{{ addslashes($member->full_name) }}'? This will permanently delete their profile and membership history." 
                                              data-confirm-title="Remove Gym Member" 
                                              data-confirm-btn="Yes, Delete Member" 
                                              data-confirm-type="danger" 
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Member" class="p-1.5 rounded-lg bg-raised hover:bg-rose-50 text-fg-muted hover:text-rose-600 border border-line dark:bg-raised dark:hover:bg-rose-500/20 dark:text-fg-muted dark:hover:text-rose-400 dark:border-line transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-fg-muted dark:text-fg-muted">
                                    <p class="text-sm">No gym members found matching your search or filters.</p>
                                    <a href="{{ route('app.members.create') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-semibold text-xs">Enroll First Member</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($members->hasPages())
                <div data-member-pagination class="p-3 sm:p-4 border-t border-line dark:border-line bg-raised dark:bg-slate-950/40">
                    {{ $members->links() }}
                </div>
            @endif
        </div>


<script type="application/json" id="member-results-data">{!! json_encode($membersMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR) !!}</script>
