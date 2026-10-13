<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('workers.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Register Daily-Wage Worker</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-[800px] mx-auto">

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-[4px] bg-red-50 border border-red-200 text-[#dc2626] text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="crm-section">
                <form method="POST" action="{{ route('workers.store') }}">
                    @csrf

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Personal & Wage Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="crm-label">Worker Full Name <span class="required-marker">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. S. Kumar" class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Mobile Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 9842155678" class="crm-input">
                            @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Standard Daily Wage Rate (₹) <span class="required-marker">*</span></label>
                            <input type="number" step="any" min="0" name="daily_rate" value="{{ old('daily_rate', 700) }}" required placeholder="e.g. 700" class="crm-input">
                            @error('daily_rate') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Field Skills & Capabilities</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-1">
                                @foreach(['cabling' => 'Cabling & Conduit', 'mounting' => 'Camera Mounting', 'termination' => 'RJ45 Termination', 'networking' => 'IP Networking', 'civil' => 'Drilling / Civil', 'helper' => 'General Helper'] as $val => $lbl)
                                    <label class="flex items-center gap-2 p-2 border border-[#ddd] rounded-[4px] cursor-pointer text-xs text-gray-700">
                                        <input type="checkbox" name="skills[]" value="{{ $val }}" class="rounded-[4px] border-[#ccc] text-[#2563eb]">
                                        <span>{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Notes / Emergency Contact / Aadhaar Details</label>
                            <textarea name="notes" rows="3" placeholder="e.g. Aadhaar number, emergency contact, UPI details" class="crm-input" style="height: auto;">{{ old('notes') }}</textarea>
                            @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                <span class="text-sm text-gray-700">Active (Available for daily shifts)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('workers.index') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
</x-app-layout>
