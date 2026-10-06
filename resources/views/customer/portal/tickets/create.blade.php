<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.tickets') }}" class="text-xs font-semibold hover:underline" style="color: var(--crm-accent, #2563eb);">← Back to Tickets</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Helpdesk</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">Report Breakdown / Service Request</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Submit a support request directly to our CCTV engineers for fast diagnostics and dispatch.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200/90 dark:border-slate-800 p-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white font-heading">New CCTV Incident Report</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Site: <strong class="text-slate-800 dark:text-slate-200">{{ $lead->customer_name }}</strong> ({{ $lead->site_address ?: 'Registered Address' }})</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                        Instant Dispatch
                    </span>
                </div>

                <form method="POST" action="{{ route('portal.tickets.store') }}" class="p-6 sm:p-8 space-y-6">
                    @csrf

                    {{-- Issue Title --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Summary / Problem Title <span class="text-rose-600 dark:text-rose-400">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" required autofocus
                               placeholder="e.g. Front Gate Camera 3 has no video feed / Black Screen"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs sm:text-sm font-medium focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                        @error('title') <div class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</div> @enderror
                    </div>

                    {{-- 2 Column Row: Issue Category & Priority --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        {{-- Issue Category --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Problem Category <span class="text-rose-600 dark:text-rose-400">*</span>
                            </label>
                            <select name="issue_type" required 
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm font-medium focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="" disabled selected>Select issue type...</option>
                                @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('issue_type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('issue_type') <div class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</div> @enderror
                        </div>

                        {{-- Priority --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Severity / Urgency <span class="text-rose-600 dark:text-rose-400">*</span>
                            </label>
                            <select name="priority" required 
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm font-medium focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="low" @selected(old('priority') === 'low')>Low - Minor query or scheduled check</option>
                                <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium - 1 Camera offline / Normal attention</option>
                                <option value="high" @selected(old('priority') === 'high')>High - Multiple cameras or NVR recording issue</option>
                                <option value="critical" @selected(old('priority') === 'critical')>Critical - Entire security system down / Emergency</option>
                            </select>
                            @error('priority') <div class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</div> @enderror
                        </div>

                    </div>

                    {{-- Affected Equipment (Optional) --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Affected Device / Location (Optional)
                        </label>
                        <select name="equipment_id" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm font-medium focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                            <option value="">-- General Site Issue / Not Listed --</option>
                            @foreach($equipmentList as $eq)
                                <option value="{{ $eq->id }}" @selected(old('equipment_id', $preselectedEquipmentId) == $eq->id)>
                                    {{ $eq->equipment_name }} [📍 {{ $eq->location_tag ?: 'Main' }}] (S/N: {{ $eq->serial_number ?: 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Specifying the exact camera helps our technicians carry the right replacement lenses and power units.</p>
                        @error('equipment_id') <div class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</div> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Detailed Symptoms & Description <span class="text-rose-600 dark:text-rose-400">*</span>
                        </label>
                        <textarea name="description" rows="4" required
                                  placeholder="Please explain what happened: when did it stop working, any beeping noises, power outage, error on mobile app..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs sm:text-sm font-medium focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">{{ old('description') }}</textarea>
                        @error('description') <div class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</div> @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                        <a href="{{ route('portal.tickets') }}" 
                           class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="crm-customer-action-btn px-5 py-2.5 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                                style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                            Submit Service Request
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
