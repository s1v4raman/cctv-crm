<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Supplier</h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-[800px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('suppliers.show', $supplier) }}" class="back-link">&larr; Back</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Edit Supplier &mdash; {{ $supplier->name }}</h2>
            </div>

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
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Supplier Name <span class="required-marker">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" class="crm-input">
                            @error('company_name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="crm-input">
                            @error('contact_person') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="crm-input">
                            @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="crm-input">
                            @error('email') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">GSTIN</label>
                            <input type="text" name="gst_number" value="{{ old('gst_number', $supplier->gst_number) }}" class="crm-input font-mono">
                            @error('gst_number') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Payment Terms</label>
                            <input type="text" name="payment_terms" value="{{ old('payment_terms', $supplier->payment_terms) }}" class="crm-input">
                            @error('payment_terms') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Address</label>
                            <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">City</label>
                            <input type="text" name="city" value="{{ old('city', $supplier->city) }}" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">State</label>
                            <input type="text" name="state" value="{{ old('state', $supplier->state) }}" class="crm-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Vendor Notes & Bank Details</label>
                        <textarea name="notes" rows="2" class="crm-input" style="height: auto;">{{ old('notes', $supplier->notes) }}</textarea>
                    </div>

                    <div class="mb-4 flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $supplier->is_active)) class="w-4 h-4 rounded-[4px] border-[#ccc] text-[#2563eb]">
                        <label for="is_active" class="text-sm text-gray-700">
                            Active Supplier
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
