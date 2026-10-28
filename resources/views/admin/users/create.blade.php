<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit']">Add New User Account</h2>
                <p class="mt-1 text-xs text-slate-400 font-mono">Register a new access account for an administrator, employee, or technician.</p>
            </div>
            <a href="{{ request('return_to', route('admin.users.index')) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 hover:text-white text-xs font-semibold transition">
                &larr; {{ request('return_to') ? (str_contains(request('return_to'), 'wizard') ? 'Back to Project Wizard' : 'Back to Project') : 'Back to Accounts' }}
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl shadow-xl overflow-hidden p-6 sm:p-8">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                    @csrf
                    @if(request('return_to', old('return_to')))
                        <input type="hidden" name="return_to" value="{{ request('return_to', old('return_to')) }}">
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3" placeholder="e.g. John Doe">
                        @error('name') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3" placeholder="e.g. john@example.com">
                        @error('email') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Account Role</label>
                        <select name="role" id="roleSelect" class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3" required>
                            <option value="" disabled {{ !request('role') && !old('role') ? 'selected' : '' }} class="bg-[#0F172A] text-slate-500">Select Role...</option>
                            <option value="admin" @selected(request('role', old('role')) === 'admin') class="bg-[#0F172A]">Admin</option>
                            <option value="staff" @selected(request('role', old('role')) === 'staff') class="bg-[#0F172A]">Employee (Staff)</option>
                            <option value="technician" @selected(request('role', old('role')) === 'technician') class="bg-[#0F172A]">Technician</option>
                            <option value="worker" @selected(request('role', old('role')) === 'worker') class="bg-[#0F172A]">Worker (Daily Wage)</option>
                        </select>
                        @error('role') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Password</label>
                        <input type="password" name="password" required
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3 font-mono" placeholder="Min. 8 characters">
                        @error('password') <div class="text-xs text-rose-400 mt-1 font-mono">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full text-sm rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3 font-mono" placeholder="Re-type password">
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg min-h-[44px] shadow-amber-500/20 transition mt-4">
                        Register Account
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
