<x-admin-layout header="Platform Users & Roles">
    <div class="space-y-6" x-data="{ showNewModal: false, editUser: null }">
        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow max-w-2xl">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user name, email, phone..." class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-red-500 focus:outline-none flex-grow">
                <select name="role" class="px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="gym_owner" {{ request('role') === 'gym_owner' ? 'selected' : '' }}>Gym Owner</option>
                    <option value="gym_manager" {{ request('role') === 'gym_manager' ? 'selected' : '' }}>Gym Manager</option>
                    <option value="trainer" {{ request('role') === 'trainer' ? 'selected' : '' }}>Trainer</option>
                    <option value="receptionist" {{ request('role') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                    <option value="accountant" {{ request('role') === 'accountant' ? 'selected' : '' }}>Accountant</option>
                    <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold">
                    Filter
                </button>
            </form>

            <button @click="showNewModal = true" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-500/10">
                + Create User Account
            </button>
        </div>

        <!-- Users Table -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-semibold">User</th>
                            <th class="py-3.5 px-4 font-semibold">Email</th>
                            <th class="py-3.5 px-4 font-semibold">Role</th>
                            <th class="py-3.5 px-4 font-semibold">Assigned Gym</th>
                            <th class="py-3.5 px-4 font-semibold">Status</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center font-bold text-amber-400 text-xs">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block">{{ $u->name }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $u->phone ?? 'No phone' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-300">
                                    {{ $u->email }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $u->role === 'super_admin' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">
                                        {{ str_replace('_', ' ', $u->role) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-300">
                                    {{ $u->tenant->name ?? 'System-Wide' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
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
                                        ]) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-semibold">
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
                                                <button type="submit" class="px-2 py-1 rounded bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 text-[10px] font-semibold transition-all">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Edit User Modal -->
        <div x-show="editUser !== null" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl" @click.away="editUser = null">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Edit User Account</h3>
                    <button @click="editUser = null" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <template x-if="editUser !== null">
                    <form :action="'/admin/users/' + editUser.id" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name *</label>
                            <input type="text" name="name" x-model="editUser.name" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Email *</label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Phone</label>
                            <input type="text" name="phone" x-model="editUser.phone" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Role</label>
                                <select name="role" x-model="editUser.role" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
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
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                                <select name="status" x-model="editUser.status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                    <option value="SUSPENDED">SUSPENDED</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Assigned Gym Tenant</label>
                            <select name="tenant_id" x-model="editUser.tenant_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="">-- No Gym (Global Super Admin) --</option>
                                @foreach($gyms as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Reset Password (leave empty to keep current)</label>
                            <input type="password" name="password" placeholder="New password..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                            <button type="button" @click="editUser = null" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white font-bold text-xs">Update User</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Add User Modal -->
        <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl" @click.away="showNewModal = false">
                <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Create New User Account</h3>
                    <button @click="showNewModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="Marcus Vance" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="user@gym.com" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Phone</label>
                        <input type="text" name="phone" placeholder="+1 555-0100" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Role *</label>
                            <select name="role" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
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
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                                <option value="SUSPENDED">SUSPENDED</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Gym Tenant</label>
                        <select name="tenant_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                            <option value="">-- None (Super Admin) --</option>
                            @foreach($gyms as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Initial Password *</label>
                        <input type="password" name="password" required value="password" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

