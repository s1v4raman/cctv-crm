<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('workers.show', $worker) }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Edit Worker &mdash; {{ $worker->name }}</h2>
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
                <form id="worker-form" method="POST" action="{{ route('workers.update', $worker) }}">
                    @csrf
                    @method('PUT')

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Personal & Wage Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="crm-label">Worker Full Name <span class="required-marker">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $worker->name) }}" required class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Mobile Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $worker->phone) }}" class="crm-input">
                            @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Standard Daily Wage Rate (₹) <span class="required-marker">*</span></label>
                            <input type="number" step="any" min="0" name="daily_rate" value="{{ old('daily_rate', $worker->daily_rate) }}" required class="crm-input">
                            @error('daily_rate') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Field Skills & Capabilities</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-1">
                                @php
                                    $currentSkills = is_array($worker->skills) ? $worker->skills : [];
                                @endphp
                                @foreach(['cabling' => 'Cabling & Conduit', 'mounting' => 'Camera Mounting', 'termination' => 'RJ45 Termination', 'networking' => 'IP Networking', 'civil' => 'Drilling / Civil', 'helper' => 'General Helper'] as $val => $lbl)
                                    <label class="flex items-center gap-2 p-2 border border-[#ddd] rounded-[4px] cursor-pointer text-xs text-gray-700">
                                        <input type="checkbox" name="skills[]" value="{{ $val }}" {{ in_array($val, $currentSkills) ? 'checked' : '' }} class="rounded-[4px] border-[#ccc] text-[#2563eb]">
                                        <span>{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Notes / Emergency Contact / Aadhaar Details</label>
                            <textarea name="notes" rows="3" class="crm-input" style="height: auto;">{{ old('notes', $worker->notes) }}</textarea>
                            @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $worker->is_active ? 'checked' : '' }} class="w-4 h-4 rounded-[4px] border-[#ccc] text-[#2563eb]">
                                <span class="text-sm text-gray-700">Active (Available for daily shifts)</span>
                            </label>
                        </div>
                    </div>
                </form>

                <form id="delete-worker-form" method="POST" action="{{ route('workers.destroy', $worker) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="flex items-center justify-between pt-4 border-t border-[#ddd] mt-4">
                    <button type="submit" form="delete-worker-form" onclick="return confirm('Are you sure you want to delete this worker?')" class="danger-link" style="background: none; border: none; cursor: pointer;">
                        Delete
                    </button>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('workers.show', $worker) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" form="worker-form" class="btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
