<x-app-layout header="Roles & Permissions">
    @php
        $tenant = auth()->user()->tenant;
        $systemRoleNames = ['gym_owner', 'gym_manager', 'receptionist', 'sales', 'trainer', 'accountant', 'staff'];
    @endphp

    <div x-data="{
        showAddRoleModal: false,
        showEditRoleModal: false,
        searchQuery: '',
        roleForm: {
            id: null,
            display_name: '',
            name: '',
            description: ''
        },
        openEditRole(role) {
            this.roleForm = {
                id: role.id,
                display_name: role.display_name,
                name: role.name,
                description: role.description || ''
            };
            this.showEditRoleModal = true;
        },
        toggleRoleColumn(roleId, selectAll) {
            const checkboxes = document.querySelectorAll(`input[data-role='${roleId}']`);
            checkboxes.forEach(cb => {
                cb.checked = selectAll;
            });
        },
        matchesSearch(text) {
            if (!this.searchQuery) return true;
            return text.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
        }
    }" class="space-y-6">

        <!-- Top Navigation Bar & Tabs (Staff vs Roles) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-4">
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Staff Tab -->
                <a href="{{ route('app.staff.index') }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 bg-surface dark:bg-surface text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-fg hover:bg-raised dark:hover:bg-raised border border-line dark:border-line">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Staff Members</span>
                </a>

                <!-- Roles & Permissions Tab (Active) -->
                <a href="{{ route('app.roles.index') }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 bg-accent text-accent-on ops-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Roles & Permissions</span>
                    <span class="px-1.5 py-0.5 rounded-full text-xs bg-white/20 dark:bg-slate-950/60 font-semibold">{{ $roles->count() }}</span>
                </a>
            </div>

            <!-- Action Button: New Role -->
            <div>
                <button type="button" @click="showAddRoleModal = true" class="px-3.5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer ops-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>New Custom Role</span>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SECTION 1: ROLES OVERVIEW TABLE            -->
        <!-- ========================================== -->
        <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
            <div class="p-4 border-b border-line dark:border-line flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-fg dark:text-white">Custom & System Roles</h3>
                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Manage roles for your gym and customize their granular permission access levels below.</p>
                </div>
                <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold">{{ $roles->count() }} Defined Roles</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[550px] text-left text-xs">
                    <thead class="bg-raised dark:bg-slate-950/60 text-fg-muted dark:text-fg-muted text-xs font-semibold border-b border-line dark:border-line">
                        <tr>
                            <th class="py-3 px-4">Role Name</th>
                            <th class="py-3 px-4">Slug / Key</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Staff Count</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-line font-medium text-fg-muted dark:text-fg-muted">
                        @foreach($roles as $r)
                            <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                <!-- Role Name -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-fg dark:text-white flex items-center gap-2">
                                        <span>{{ $r->display_name }}</span>
                                        @if($r->is_system)
                                            <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted border border-line dark:border-line">Default</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">Custom</span>
                                        @endif
                                    </div>
                                    @if($r->description)
                                        <div class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">{{ $r->description }}</div>
                                    @endif
                                </td>

                                <!-- Slug / Key -->
                                <td class="py-3 px-4 font-mono text-xs text-indigo-600 dark:text-indigo-300">
                                    {{ $r->name }}
                                </td>

                                <!-- Type -->
                                <td class="py-3 px-4 text-xs text-fg-muted dark:text-fg-muted">
                                    {{ $r->is_system ? 'System Role' : 'Custom Gym Role' }}
                                </td>

                                <!-- Staff Count -->
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ ($r->staff_count ?? 0) > 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ $r->staff_count ?? 0 }} Staff
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openEditRole({{ Js::from($r) }})" title="Edit Role" class="p-1.5 rounded-lg bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white transition-colors cursor-pointer border border-line dark:border-line">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        @if(!$r->is_system)
                                            <form action="{{ route('app.roles.delete', $r->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to delete custom role '{{ addslashes($r->display_name) }}'? Staff assigned to this role will need new permissions." 
                                                  data-confirm-title="Delete Custom Role" 
                                                  data-confirm-btn="Yes, Delete Role" 
                                                  data-confirm-type="danger" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Role" class="p-1.5 rounded-lg bg-rose-50 dark:bg-raised hover:bg-rose-100 dark:hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors cursor-pointer border border-rose-200 dark:border-line">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SECTION 2: PERMISSION MATRIX FORM          -->
        <!-- ========================================== -->
        <form action="{{ route('app.roles.matrix.update') }}" method="POST" class="space-y-4">
            @csrf

            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <!-- Matrix Header Banner -->
                <div class="p-4 border-b border-line dark:border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface dark:bg-surface">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xs font-semibold text-fg dark:text-white">Granular Permission Matrix</h3>
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">Role Access Control</span>
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 dark:bg-indigo-400"></span>
                                <span>{{ $tenant->activeSubscription?->plan?->name ?? 'Free Forever' }} Plan Features</span>
                            </span>
                        </div>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">
                            Separated Create, View, Edit, Delete and Financials access. Check the boxes to grant or restrict specific actions for each role.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
                        <!-- Search / Filter Input -->
                        <div class="relative">
                            <input type="text" 
                                   x-model="searchQuery" 
                                   placeholder="Filter permissions..." 
                                   class="pl-8 pr-3 py-1.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none w-44 sm:w-56">
                            <svg class="w-3.5 h-3.5 text-fg-muted absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Save Matrix</span>
                        </button>
                    </div>
                </div>

                <!-- Matrix Table -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-xs border-collapse">
                        <!-- Table Head: Roles Columns -->
                        <thead class="bg-raised dark:bg-surface text-fg-muted dark:text-fg-muted text-xs font-semibold border-b border-line dark:border-line sticky top-0 z-20">
                            <tr>
                                <th class="py-3 px-4 min-w-[280px] bg-raised dark:bg-surface">PERMISSION / ACTION</th>
                                @foreach($roles as $role)
                                    <th class="py-3 px-3 text-center min-w-[120px] border-l border-line dark:border-line bg-raised dark:bg-surface">
                                        <div class="font-semibold text-fg dark:text-white text-xs">{{ $role->display_name }}</div>
                                        @if(!$role->is_system)
                                            <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold lowercase tracking-normal">custom</div>
                                        @endif
                                        <div class="flex items-center justify-center gap-1.5 mt-1 font-normal lowercase">
                                            <button type="button" @click="toggleRoleColumn({{ $role->id }}, true)" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">all</button>
                                            <span class="text-fg-muted dark:text-fg-muted">|</span>
                                            <button type="button" @click="toggleRoleColumn({{ $role->id }}, false)" class="text-xs text-fg-muted hover:underline">none</button>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-line dark:divide-line font-medium">
                            @foreach($permissionGroups as $groupKey => $groupData)
                                <!-- Group Header Row -->
                                <tr class="bg-raised dark:bg-slate-950/80 border-t-2 border-b border-line dark:border-line font-mono text-xs text-indigo-700 dark:text-indigo-300 font-semibold">
                                    <td colspan="{{ $roles->count() + 1 }}" class="py-2.5 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                            <span class="font-sans text-xs font-semibold text-fg dark:text-fg">{{ $groupData['label'] }}</span>
                                            <span class="text-fg-muted lowercase font-mono">({{ $groupKey }})</span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Permission Items in Group -->
                                @foreach($groupData['permissions'] as $p)
                                    @php
                                        $permObj = $allPermissions->get($p['name']);
                                        $permId = $permObj?->id;
                                        $pName = strtolower($p['name']);
                                        
                                        // Tag badge determination
                                        $badgeType = 'VIEW';
                                        $badgeClass = 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/20';
                                        if (str_contains($pName, 'campaign') || str_contains($pName, 'create') || str_contains($pName, 'add') || str_contains($pName, 'record') || str_contains($pName, 'book')) {
                                            $badgeType = str_contains($pName, 'campaign') ? 'CAMPAIGN' : 'CREATE';
                                            $badgeClass = 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20';
                                        } elseif (str_contains($pName, 'inbox')) {
                                            $badgeType = 'INBOX';
                                            $badgeClass = 'bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-500/20';
                                        } elseif (str_contains($pName, 'contacts')) {
                                            $badgeType = 'AUDIENCE';
                                            $badgeClass = 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20';
                                        } elseif (str_contains($pName, 'edit') || str_contains($pName, 'update') || str_contains($pName, 'manage') || str_contains($pName, 'freeze') || str_contains($pName, 'settings')) {
                                            $badgeType = str_contains($pName, 'settings') ? 'CONFIG' : 'EDIT';
                                            $badgeClass = 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20';
                                        } elseif (str_contains($pName, 'delete') || str_contains($pName, 'cancel') || str_contains($pName, 'refund')) {
                                            $badgeType = 'DELETE';
                                            $badgeClass = 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20';
                                        } elseif (str_contains($pName, 'financials') || str_contains($pName, 'balance_sheet')) {
                                            $badgeType = 'FINANCE';
                                            $badgeClass = 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-500/20';
                                        }
                                    @endphp
                                    <tr x-show="matchesSearch('{{ addslashes($p['display_name'] . ' ' . $p['name'] . ' ' . $p['description'] . ' ' . $groupData['label']) }}')"
                                        class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                        <!-- Permission Info (Name, Description, Code) -->
                                        <td class="py-2.5 px-4">
                                            <div class="flex items-center gap-2">
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black tracking-wider uppercase border {{ $badgeClass }}">
                                                    {{ $badgeType }}
                                                </span>
                                                <span class="font-semibold text-fg dark:text-white text-xs">{{ $p['display_name'] }}</span>
                                            </div>
                                            <div class="text-xs text-fg-muted dark:text-fg-muted leading-snug mt-0.5">{{ $p['description'] }}</div>
                                            <div class="font-mono text-xs text-indigo-600 dark:text-indigo-400/70 mt-0.5">{{ $p['name'] }}</div>
                                        </td>

                                        <!-- Checkboxes for each role -->
                                        @foreach($roles as $role)
                                            @php
                                                $hasPerm = $permObj && $role->permissions->contains('id', $permId);
                                            @endphp
                                            <td class="py-2.5 px-3 text-center border-l border-line dark:border-line">
                                                @if($permId)
                                                    <label class="inline-flex items-center justify-center p-1 rounded-md hover:bg-raised dark:hover:bg-raised cursor-pointer">
                                                        <input type="checkbox" 
                                                               name="matrix[{{ $role->id }}][]" 
                                                               value="{{ $permId }}" 
                                                               data-role="{{ $role->id }}"
                                                               {{ $hasPerm ? 'checked' : '' }}
                                                               class="w-4 h-4 rounded border-line dark:border-line bg-surface dark:bg-surface text-indigo-600 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                                                    </label>
                                                @else
                                                    <span class="text-fg-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Save Button Bar -->
                <div class="p-4 border-t border-line dark:border-line bg-raised dark:bg-slate-950/60 flex items-center justify-between">
                    <span class="text-xs text-fg-muted dark:text-fg-muted">Remember to save changes after modifying permissions.</span>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Permission Matrix</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- ========================================== -->
        <!-- MODAL 1: ADD CUSTOM ROLE                   -->
        <!-- ========================================== -->
        <div x-show="showAddRoleModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 space-y-4" @click.away="showAddRoleModal = false">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-fg dark:text-white">Create Custom Role</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Add a custom role with tailored permissions for your gym team.</p>
                    </div>
                    <button type="button" @click="showAddRoleModal = false" class="p-1 rounded-lg text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.roles.store') }}" method="POST" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Role Title / Display Name *</label>
                        <input type="text" name="display_name" required placeholder="e.g. Floor Supervisor, Diet Coach" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Role Identifier / Slug (Optional)</label>
                        <input type="text" name="name" placeholder="e.g. floor_supervisor (leave blank to auto-generate)" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Brief summary of duties and responsibilities..." class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="showAddRoleModal = false" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-semibold transition-all cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer ops-primary">
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: EDIT ROLE                         -->
        <!-- ========================================== -->
        <div x-show="showEditRoleModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 space-y-4" @click.away="showEditRoleModal = false">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-fg dark:text-white">Edit Role</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Update role title and descriptive summary.</p>
                    </div>
                    <button type="button" @click="showEditRoleModal = false" class="p-1 rounded-lg text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('/app/roles') }}/' + roleForm.id" method="POST" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Role Title / Display Name *</label>
                        <input type="text" name="display_name" x-model="roleForm.display_name" required class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Role Identifier (Slug)</label>
                        <input type="text" :value="roleForm.name" disabled class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-slate-950/50 border border-line dark:border-line text-fg-muted text-xs cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Description</label>
                        <textarea name="description" x-model="roleForm.description" rows="2" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="showEditRoleModal = false" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-semibold transition-all cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer ops-primary">
                            Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
