<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit']">Edit User Account: {{ $user->name }}</h2>
                <p class="mt-1 text-xs text-slate-400 font-mono">Update account details, role and security options for this user.</p>
            </div>
            <a href="{{ request('return_to', route('admin.users.index')) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 hover:text-white text-xs font-semibold transition">
                &larr; {{ request('return_to') && str_contains(request('return_to'), 'employee') ? 'Back to Employee Hub' : 'Back to Accounts' }}
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl shadow-xl overflow-hidden p-6 sm:p-8">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    @if(request('return_to', old('return_to')))
                        <input type="hidden" name="return_to" value="{{ request('return_to', old('return_to')) }}">
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3" placeholder="e.g. John Doe">
                        @error('name') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3" placeholder="e.g. john@example.com">
                        @error('email') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Account Role</label>
                        <select name="role" id="roleSelect" class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3" required>
                            <option value="admin" @selected(old('role', $user->role) === 'admin') class="bg-[#0F172A]">Admin</option>
                            <option value="staff" @selected(old('role', $user->role) === 'staff') class="bg-[#0F172A]">Employee (Staff)</option>
                            <option value="technician" @selected(old('role', $user->role) === 'technician') class="bg-[#0F172A]">Technician</option>
                            <option value="worker" @selected(old('role', $user->role) === 'worker') class="bg-[#0F172A]">Worker (Daily Wage)</option>
                        </select>
                        @error('role') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div class="pt-5 border-t border-white/5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-1">Change Password (Optional)</h4>
                        <p class="text-[11px] text-slate-400 font-mono mb-4">Leave password fields blank if you do not want to change the password.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">New Password</label>
                        <input type="password" name="password"
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3 font-mono" placeholder="Min. 8 characters">
                        @error('password') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3 font-mono" placeholder="Re-type new password">
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg min-h-[44px] shadow-amber-500/20 transition mt-4">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
