<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('workers.show', $worker) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Edit Worker: {{ $worker->name }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        <form method="POST" action="{{ route('workers.update', $worker) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                <h3 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    Personal &amp; Wage Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Worker Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $worker->name) }}" required class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Mobile Phone Number
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $worker->phone) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-sans mb-1.5">
                            Standard Daily Wage Rate <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="flex items-stretch rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition overflow-hidden shadow-xs">
                            <span class="px-3.5 py-2.5 bg-slate-100 dark:bg-slate-700/60 border-r border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold text-sm select-none">₹</span>
                            <input type="number" step="any" min="0" name="daily_rate" value="{{ old('daily_rate', $worker->daily_rate) }}" required class="w-full px-3.5 py-2.5 text-sm font-bold font-mono bg-transparent border-0 outline-none text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-0">
                        </div>
                    </div>

                    <div class="sm:col-span-2 pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                            Field Skills &amp; Capabilities
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @php
                                $currentSkills = is_array($worker->skills) ? $worker->skills : [];
                            @endphp
                            @foreach(['cabling' => 'Cabling & Conduit', 'mounting' => 'Camera Mounting', 'termination' => 'RJ45 Termination', 'networking' => 'IP Networking', 'civil' => 'Drilling / Civil', 'helper' => 'General Helper'] as $val => $lbl)
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" name="skills[]" value="{{ $val }}" {{ in_array($val, $currentSkills) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                                    <span>{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Notes / Emergency Contact / Aadhaar Details
                        </label>
                        <textarea name="notes" rows="3" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes', $worker->notes) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $worker->is_active ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Active (Available for daily shifts)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <button type="button" onclick="if(confirm('Are you sure you want to remove this worker?')) document.getElementById('delete-worker-form').submit();" class="text-rose-600 hover:text-rose-800 text-xs font-bold">
                    Delete Worker
                </button>

                <div class="flex items-center gap-3">
                    <a href="{{ route('workers.show', $worker) }}" class="btn-secondary text-sm">Cancel</a>
                    <button type="submit" class="btn-primary text-sm shadow-md px-6 py-2.5">Update Worker</button>
                </div>
            </div>
        </form>

        <form id="delete-worker-form" method="POST" action="{{ route('workers.destroy', $worker) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-app-layout>
