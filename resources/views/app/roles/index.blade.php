<x-app-layout header="Roles & Permissions">
    @php
        $tenant = auth()->user()->tenant;
        $systemRoleNames = ['gym_manager', 'receptionist', 'trainer', 'accountant', 'staff'];
    @endphp

    <div x-data="{
        showAddRoleModal: false,
        showEditRoleModal: false,
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
        }
    }" class="space-y-4">

        <!-- Top Navigation Bar & Tabs (Staff vs Roles) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
            <div class="flex items-center gap-1.5">
                <!-- Staff Tab -->
                <a href="{{ route('app.staff.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Staff Members</span>
                </a>

                <!-- Roles & Permissions Tab (Active) -->
                <a href="{{ route('app.roles.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-indigo-600 text-white shadow border border-indigo-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Roles & Permissions</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-950/60 font-black">{{ $roles->count() }}</span>
                </a>
            </div>

            <!-- Action Button: New Role -->
            <div>
                <button @click="showAddRoleModal = true" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>New Role</span>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SECTION 1: ROLES OVERVIEW TABLE            -->
        <!-- ========================================== -->
        <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden shadow">
            <div class="p-3.5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Custom & System Roles</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Manage roles for your gym and customize their permission access levels below.</p>
                </div>
                <span class="text-[10px] text-slate-400 font-semibold">{{ $roles->count() }} Defined Roles</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3.5">Role Name</th>
                            <th class="py-2.5 px-3.5">Slug / Key</th>
                            <th class="py-2.5 px-3.5">Type</th>
                            <th class="py-2.5 px-3.5">Staff Count</th>
                            <th class="py-2.5 px-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @foreach($roles as $r)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <!-- Role Name -->
                                <td class="py-2.5 px-3.5">
                                    <div class="font-bold text-white flex items-center gap-2">
                                        <span>{{ $r->display_name }}</span>
                                        @if($r->is_system)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Default</span>
                                        @else
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Custom</span>
                                        @endif
                                    </div>
                                    @if($r->description)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $r->description }}</div>
                                    @endif
                                </td>

                                <!-- Slug / Key -->
                                <td class="py-2.5 px-3.5 font-mono text-[11px] text-indigo-300">
                                    {{ $r->name }}
                                </td>

                                <!-- Type -->
                                <td class="py-2.5 px-3.5 text-[11px] text-slate-400">
                                    {{ $r->is_system ? 'System Role' : 'Custom Gym Role' }}
                                </td>

                                <!-- Staff Count -->
                                <td class="py-2.5 px-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($r->staff_count ?? 0) > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $r->staff_count ?? 0 }} Staff
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-2.5 px-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="openEditRole({{ Js::from($r) }})" title="Edit Role" class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        @if(!$r->is_system)
                                            <form action="{{ route('app.roles.delete', $r->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete custom role {{ $r->display_name }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Role" class="p-1 rounded bg-slate-800 hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 transition-colors">
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
        <form action="{{ route('app.roles.matrix.update') }}" method="POST" class="space-y-3">
            @csrf

            <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden shadow">
                <!-- Matrix Header Banner -->
                <div class="p-3.5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-slate-900">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Permission Matrix</h3>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Role Access Control</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                <span>{{ $tenant->activeSubscription?->plan?->name ?? 'Free Forever' }} Plan Features</span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Only permissions enabled in your gym's active subscription plan are shown below. Check the boxes to grant access to each role.
                        </p>
                    </div>

                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow flex items-center gap-1.5 transition-all self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Permission Matrix</span>
                    </button>
                </div>

                <!-- Matrix Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <!-- Table Head: Roles Columns -->
                        <thead class="bg-slate-950 text-slate-300 uppercase tracking-wider text-[10px] font-black border-b border-slate-800 sticky top-0 z-20">
                            <tr>
                                <th class="py-3 px-4 min-w-[260px] bg-slate-950">PERMISSION</th>
                                @foreach($roles as $role)
                                    <th class="py-3 px-3 text-center min-w-[110px] border-l border-slate-800/80 bg-slate-950">
                                        <div class="font-extrabold text-white text-[11px] uppercase tracking-wide">{{ $role->display_name }}</div>
                                        @if(!$role->is_system)
                                            <div class="text-[9px] text-indigo-400 font-bold lowercase tracking-normal">custom</div>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @foreach($permissionGroups as $groupKey => $groupData)
                                <!-- Group Header Row -->
                                <tr class="bg-slate-950/80 border-t-2 border-b border-slate-800 font-mono text-[10px] text-indigo-300 font-bold uppercase tracking-wider">
                                    <td colspan="{{ $roles->count() + 1 }}" class="py-2 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                            <span class="font-sans text-xs font-bold text-slate-200">{{ $groupData['label'] }}</span>
                                            <span class="text-slate-500 lowercase font-mono">({{ $groupKey }})</span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Permission Items in Group -->
                                @foreach($groupData['permissions'] as $p)
                                    @php
                                        $permObj = $allPermissions->get($p['name']);
                                        $permId = $permObj?->id;
                                    @endphp
                                    <tr class="hover:bg-slate-800/30 transition-colors">
                                        <!-- Permission Info (Name, Description, Code) -->
                                        <td class="py-2.5 px-4">
                                            <div class="font-bold text-white text-xs">{{ $p['display_name'] }}</div>
                                            <div class="text-[10px] text-slate-400 leading-snug">{{ $p['description'] }}</div>
                                            <div class="font-mono text-[9px] text-indigo-400/70 mt-0.5">{{ $p['name'] }}</div>
                                        </td>

                                        <!-- Checkboxes for each role -->
                                        @foreach($roles as $role)
                                            @php
                                                $hasPerm = $permObj && $role->permissions->contains('id', $permId);
                                            @endphp
                                            <td class="py-2.5 px-3 text-center border-l border-slate-800/60">
                                                @if($permId)
                                                    <label class="inline-flex items-center justify-center p-1 rounded-md hover:bg-slate-800 cursor-pointer">
                                                        <input type="checkbox" 
                                                               name="matrix[{{ $role->id }}][]" 
                                                               value="{{ $permId }}" 
                                                               {{ $hasPerm ? 'checked' : '' }}
                                                               class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                                                    </label>
                                                @else
                                                    <span class="text-slate-600">-</span>
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
                <div class="p-3.5 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Remember to save changes after modifying permissions.</span>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Permission Matrix</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- ========================================== -->
        <!-- MODAL 1: ADD CUSTOM ROLE                   -->
        <!-- ========================================== -->
        <div x-show="showAddRoleModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-3" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-4 sm:p-5 shadow-2xl space-y-4" @click.away="showAddRoleModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white">Create Custom Role</h3>
                        <p class="text-[11px] text-slate-400">Add a custom role with tailored permissions for your gym team.</p>
                    </div>
                    <button @click="showAddRoleModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.roles.store') }}" method="POST" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Role Title / Display Name *</label>
                        <input type="text" name="display_name" required placeholder="e.g. Floor Supervisor, Diet Coach" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Role Identifier / Slug (Optional)</label>
                        <input type="text" name="name" placeholder="e.g. floor_supervisor (leave blank to auto-generate)" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Brief summary of duties and responsibilities..." class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="showAddRoleModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow transition-all">
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: EDIT ROLE                         -->
        <!-- ========================================== -->
        <div x-show="showEditRoleModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-3" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-4 sm:p-5 shadow-2xl space-y-4" @click.away="showEditRoleModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white">Edit Role</h3>
                        <p class="text-[11px] text-slate-400">Update role title and descriptive summary.</p>
                    </div>
                    <button @click="showEditRoleModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('/app/roles') }}/' + roleForm.id" method="POST" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Role Title / Display Name *</label>
                        <input type="text" name="display_name" x-model="roleForm.display_name" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Role Identifier (Slug)</label>
                        <input type="text" :value="roleForm.name" disabled class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950/50 border border-slate-800 text-slate-400 text-xs cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Description</label>
                        <textarea name="description" x-model="roleForm.description" rows="2" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="showEditRoleModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow transition-all">
                            Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

