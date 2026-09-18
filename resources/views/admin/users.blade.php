<x-admin-layout header="Platform Users &amp; Roles">
    <div class="space-y-6" x-data="{ showNewModal: false, editUser: null }">
        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow max-w-2xl">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user name, email, phone..." class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow shadow-xs">
            <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap items-center gap-2 sm:gap-3 flex-grow max-w-2xl w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user name, email, phone..." class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow min-w-[140px] shadow-xs">
                <select name="role" class="px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-white text-xs focus:border-red-500 focus:outline-none shadow-xs" onchange="this.form.submit()">
                    <option value="" {{ ($selectedRole ?? '') === '' || ($selectedRole ?? '') === 'all' ? 'selected' : '' }}>All Roles</option>
                    <option value="gym_owner" {{ ($selectedRole ?? '') === 'gym_owner' ? 'selected' : '' }}>Gym Owner</option>
                    <option value="super_admin" {{ ($selectedRole ?? '') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="gym_manager" {{ ($selectedRole ?? '') === 'gym_manager' ? 'selected' : '' }}>Gym Manager</option>
                    <option value="trainer" {{ ($selectedRole ?? '') === 'trainer' ? 'selected' : '' }}>Trainer</option>
                    <option value="receptionist" {{ ($selectedRole ?? '') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                    <option value="accountant" {{ ($selectedRole ?? '') === 'accountant' ? 'selected' : '' }}>Accountant</option>
                    <option value="staff" {{ ($selectedRole ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white text-xs font-semibold border border-slate-300 dark:border-slate-700 transition-colors shadow-xs cursor-pointer">
                    Filter
                </button>
            </form>

            <button @click="showNewModal = true" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-600/20 transition-all cursor-pointer shrink-0">
            <button @click="showNewModal = true" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-red-600/20 transition-all cursor-pointer shrink-0">
                + Create User Account
            </button>
        </div>

        <!-- Users Table -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs dark:shadow-xl transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">User</th>
                            <th class="py-3.5 px-4 font-semibold">Email</th>
                            <th class="py-3.5 px-4 font-semibold">Role</th>
                            <th class="py-3.5 px-4 font-semibold">Assigned Gym</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-amber-600 dark:text-amber-400 text-xs">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white block">{{ $u->name }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $u->phone ?? 'No phone' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                                    {{ $u->email }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $u->role === 'super_admin' ? 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                        {{ str_replace('_', ' ', $u->role) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                    {{ $u->tenant->name ?? 'System-Wide' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                                        {{ $u->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editUser = {{ json_encode([
                                            'id' => $u->id,
                                            'name' => $u->name,
                                            'email' => $u->email,
                                            'phone' => $u->phone,
                                            'role' => $u->role,
                                            'status' => $u->status,
                                            'tenant_id' => $u->tenant_id,
                                        ]) }}" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 text-[10px] font-semibold cursor-pointer">
                                            Edit
                                        </button>

                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('admin.users.delete', $u->id) }}" method="POST"
                                                  data-confirm="Are you sure you want to delete user '{{ $u->name }}'? This action cannot be undone."
                                                  data-confirm-title="Delete User"
                                                  data-confirm-btn="Delete User"
                                                  data-confirm-type="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 rounded bg-red-500/10 hover:bg-red-600 hover:text-white text-red-600 dark:text-red-400 border border-red-500/20 text-[10px] font-semibold transition-all cursor-pointer">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Edit User Modal -->
        <div x-show="editUser !== null" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl transition-colors" @click.away="editUser = null">
        <div x-show="editUser !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl transition-colors" @click.away="editUser = null">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit User Account</h3>
                    <button @click="editUser = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                    <button @click="editUser = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer p-1">✕</button>
                </div>

                <template x-if="editUser !== null">
                    <form :action="'/admin/users/' + editUser.id" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                            <input type="text" name="name" x-model="editUser.name" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email *</label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone</label>
                            <input type="text" name="phone" x-model="editUser.phone" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Role</label>
                                <select name="role" x-model="editUser.role" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                    <option value="super_admin">Super Admin</option>
                                    <option value="gym_owner">Gym Owner</option>
                                    <option value="gym_manager">Gym Manager</option>
                                    <option value="trainer">Trainer</option>
                                    <option value="receptionist">Receptionist</option>
                                    <option value="accountant">Accountant</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select name="status" x-model="editUser.status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                    <option value="SUSPENDED">SUSPENDED</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Assigned Gym Tenant</label>
                            <select name="tenant_id" x-model="editUser.tenant_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="">-- No Gym (Global Super Admin) --</option>
                                @foreach($gyms as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Reset Password (leave empty to keep current)</label>
                            <input type="password" name="password" placeholder="New password..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="editUser = null" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Update User</button>
                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="editUser = null" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Update User</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Add User Modal -->
        <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl transition-colors" @click.away="showNewModal = false">
        <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-2xl transition-colors" @click.away="showNewModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Create New User Account</h3>
                    <button @click="showNewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">✕</button>
                    <button @click="showNewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer p-1">✕</button>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="Marcus Vance" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="user@gym.com" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone</label>
                        <input type="text" name="phone" placeholder="+1 555-0100" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Role *</label>
                            <select name="role" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="super_admin">Super Admin</option>
                                <option value="gym_owner">Gym Owner</option>
                                <option value="gym_manager">Gym Manager</option>
                                <option value="trainer">Trainer</option>
                                <option value="receptionist">Receptionist</option>
                                <option value="accountant">Accountant</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                                <option value="SUSPENDED">SUSPENDED</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gym Tenant</label>
                        <select name="tenant_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                            <option value="">-- None (Super Admin) --</option>
                            @foreach($gyms as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Initial Password *</label>
                        <input type="password" name="password" required value="password" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Create Account</button>
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showNewModal = false" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/20 cursor-pointer">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
