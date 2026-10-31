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
                <form id="worker-form" method="POST" action="{{ route('workers.update', $worker) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Personal & Wage Details</h3>

                    {{-- Worker Profile Photo / Login Photo --}}
                    <div class="mb-5 p-3 rounded bg-slate-50 border border-slate-200 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-slate-200 border-2 border-slate-300 flex items-center justify-center overflow-hidden shrink-0 shadow-sm" id="photo-preview-wrap">
                            @if($worker->photo_url)
                                <img id="photo-preview" src="{{ $worker->photo_url }}" alt="{{ $worker->name }}" class="w-full h-full object-cover">
                                <span id="photo-placeholder" class="text-xs font-semibold text-slate-500 uppercase hidden">Photo</span>
                            @else
                                <span id="photo-placeholder" class="text-xs font-semibold text-slate-500 uppercase">Photo</span>
                                <img id="photo-preview" alt="Worker Photo" class="w-full h-full object-cover hidden">
                            @endif
                        </div>
                        <div class="flex-1">
                            <label for="worker-photo-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                                Worker Profile Photo (Login & Verification Photo)
                            </label>
                            <input type="file" name="photo" id="worker-photo-input" accept="image/*" class="text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border file:border-slate-300 file:text-xs file:font-semibold file:bg-white hover:file:bg-slate-50 cursor-pointer">
                            <p class="text-[11px] text-slate-500 mt-0.5">Used to verify identity against daily attendance punch photos. Max 5MB (JPG, PNG).</p>
                            @if($worker->photo_path)
                                <label class="inline-flex items-center gap-1.5 mt-2 cursor-pointer text-xs text-red-600">
                                    <input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300 text-red-600">
                                    <span>Remove current photo</span>
                                </label>
                            @endif
                            @error('photo') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="crm-label">Worker Full Name <span class="required-marker">*</span></label>
                            <input aria-label="Worker Full Name" type="text" name="name" value="{{ old('name', $worker->name) }}" required class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Mobile Phone Number</label>
                            <input aria-label="Mobile Phone Number" type="text" name="phone" value="{{ old('phone', $worker->phone) }}" class="crm-input">
                            @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Standard Daily Wage Rate (₹) <span class="required-marker">*</span></label>
                            <input aria-label="Standard Daily Wage Rate (₹)" type="number" step="any" min="0" name="daily_rate" value="{{ old('daily_rate', $worker->daily_rate) }}" required class="crm-input">
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
                                        <input type="checkbox" name="skills[]" value="{{ $val }}" {{ in_array($val, $currentSkills) ? 'checked' : '' }} class="rounded-[4px] border-[#ccc] text-blue-600">
                                        <span>{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Notes / Emergency Contact / Aadhaar Details</label>
                            <textarea aria-label="Notes / Emergency Contact / Aadhaar Details" name="notes" rows="3" class="crm-input" style="height: auto;">{{ old('notes', $worker->notes) }}</textarea>
                            @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $worker->is_active ? 'checked' : '' }} class="w-4 h-4 rounded-[4px] border-[#ccc] text-blue-600">
                                <span class="text-sm text-gray-700">Active (Available for daily shifts)</span>
                            </label>
                        </div>
                    </div>
                </form>

                @if(auth()->user()->isAdmin())
                <form id="delete-worker-form" method="POST" action="{{ route('workers.destroy', $worker) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-[#ddd] mt-4">
                    @if(auth()->user()->isAdmin())
                    <button type="submit" form="delete-worker-form" data-confirm="Are you sure you want to delete this worker?" class="danger-link" style="background: none; border: none; cursor: pointer;">
                        Delete
                    </button>
                    @endif

                    <div class="flex items-center gap-3">
                        <a href="{{ route('workers.show', $worker) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" form="worker-form" class="btn-primary">Save</button>
                    </div>
                </div>
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
