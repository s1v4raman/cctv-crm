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
                <form method="POST" action="{{ route('workers.store') }}" enctype="multipart/form-data">
                    @csrf

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Personal & Wage Details</h3>

                    {{-- Worker Profile Photo / Login Photo --}}
                    <div class="mb-5 p-3 rounded bg-slate-50 border border-slate-200 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-slate-200 border-2 border-slate-300 flex items-center justify-center overflow-hidden shrink-0 shadow-sm" id="photo-preview-wrap">
                            <span id="photo-placeholder" class="text-xs font-semibold text-slate-500 uppercase">Photo</span>
                            <img id="photo-preview" alt="Worker Photo" class="w-full h-full object-cover hidden">
                        </div>
                        <div class="flex-1">
                            <label for="worker-photo-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                                Worker Profile Photo (Login & Verification Photo)
                            </label>
                            <input type="file" name="photo" id="worker-photo-input" accept="image/*" class="text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border file:border-slate-300 file:text-xs file:font-semibold file:bg-white hover:file:bg-slate-50 cursor-pointer">
                            <p class="text-[11px] text-slate-500 mt-0.5">Used to verify identity against daily attendance punch photos. Max 5MB (JPG, PNG).</p>
                            @error('photo') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="crm-label">Worker Full Name <span class="required-marker">*</span></label>
                            <input aria-label="Worker Full Name" type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. S. Kumar" class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Mobile Phone Number</label>
                            <input aria-label="Mobile Phone Number" type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 9842155678" class="crm-input">
                            @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Standard Daily Wage Rate (₹) <span class="required-marker">*</span></label>
                            <input aria-label="Standard Daily Wage Rate (₹)" type="number" step="any" min="0" name="daily_rate" value="{{ old('daily_rate', 700) }}" required placeholder="e.g. 700" class="crm-input">
                            @error('daily_rate') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Field Skills & Capabilities</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-1">
                                @foreach(['cabling' => 'Cabling & Conduit', 'mounting' => 'Camera Mounting', 'termination' => 'RJ45 Termination', 'networking' => 'IP Networking', 'civil' => 'Drilling / Civil', 'helper' => 'General Helper'] as $val => $lbl)
                                    <label class="flex items-center gap-2 p-2 border border-[#ddd] rounded-[4px] cursor-pointer text-xs text-gray-700">
                                        <input type="checkbox" name="skills[]" value="{{ $val }}" class="rounded-[4px] border-[#ccc] text-blue-600">
                                        <span>{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Notes / Emergency Contact / Aadhaar Details</label>
                            <textarea aria-label="Notes / Emergency Contact / Aadhaar Details" name="notes" rows="3" placeholder="e.g. Aadhaar number, emergency contact, UPI details" class="crm-input" style="height: auto;">{{ old('notes') }}</textarea>
                            @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded-[4px] border-[#ccc] text-blue-600">
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

    <script>
        document.getElementById('worker-photo-input')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const img = document.getElementById('photo-preview');
                    const placeholder = document.getElementById('photo-placeholder');
                    img.src = evt.target.result;
                    img.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-app-layout>
