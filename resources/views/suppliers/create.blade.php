<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">New Supplier</h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-[800px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('suppliers.index') }}" class="back-link">&larr; Back</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">New Supplier</h2>
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
                <form method="POST" action="{{ route('suppliers.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Supplier Name <span class="required-marker">*</span></label>
                            <input aria-label="Supplier Name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Hikvision India Central" required class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Company Name</label>
                            <input aria-label="Company Name" type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Prama Hikvision Pvt Ltd" class="crm-input">
                            @error('company_name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Contact Person</label>
                            <input aria-label="Contact Person" type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Ramesh Sharma" class="crm-input">
                            @error('contact_person') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Phone</label>
                            <input aria-label="Phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 9876543210" class="crm-input">
                            @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Email</label>
                            <input aria-label="Email" type="email" name="email" value="{{ old('email') }}" placeholder="e.g. orders@supplier.com" class="crm-input">
                            @error('email') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">GSTIN</label>
                            <input aria-label="GSTIN" type="text" name="gst_number" value="{{ old('gst_number') }}" placeholder="e.g. 27AAAAA0000A1Z5" class="crm-input font-mono">
                            @error('gst_number') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Payment Terms</label>
                            <input aria-label="Payment Terms" type="text" name="payment_terms" value="{{ old('payment_terms', 'Net 30 Days') }}" placeholder="e.g. Net 30 Days" class="crm-input">
                            @error('payment_terms') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Address</label>
                        <textarea aria-label="Address" name="address" rows="3" class="crm-input" style="height: auto;" placeholder="e.g. 42 Industrial Area, Guindy, Chennai">{{ old('address') }}</textarea>
                        @error('address') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">City</label>
                            <input aria-label="City" type="text" name="city" value="{{ old('city') }}" placeholder="e.g. Chennai" class="crm-input">
                            @error('city') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">State</label>
                            <input aria-label="State" type="text" name="state" value="{{ old('state') }}" placeholder="e.g. Tamil Nadu" class="crm-input">
                            @error('state') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Bank & Financial Account Details (For Reference & Offline Transfers) --}}
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 mb-4">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-3">Bank & Payment Details (For Offline Transfers)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="crm-label">Bank Name</label>
                                <input aria-label="Bank Name" type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. HDFC Bank, ICICI Bank" class="crm-input">
                                @error('bank_name') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="crm-label">Account Number</label>
                                <input aria-label="Account Number" type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="e.g. 50200012345678" class="crm-input font-mono">
                                @error('bank_account_number') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="crm-label">IFSC Code</label>
                                <input aria-label="IFSC Code" type="text" name="bank_ifsc" value="{{ old('bank_ifsc') }}" placeholder="e.g. HDFC0001234" class="crm-input font-mono uppercase">
                                @error('bank_ifsc') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="crm-label">Branch Name</label>
                                <input aria-label="Branch Name" type="text" name="bank_branch" value="{{ old('bank_branch') }}" placeholder="e.g. Guindy Industrial Branch" class="crm-input">
                                @error('bank_branch') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="crm-label">UPI ID / VPA</label>
                                <input aria-label="UPI ID / VPA" type="text" name="upi_id" value="{{ old('upi_id') }}" placeholder="e.g. vendor@hdfcbank" class="crm-input font-mono">
                                @error('upi_id') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Product Categories & Brands Supplied --}}
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 mb-4">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-3">Supplied Hardware & Categories</h4>
                        <div class="mb-4">
                            <label class="crm-label mb-2">Hardware Product Categories</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                @foreach(['CCTV Cameras & Recorders', 'Hard Disks & Storage', 'PoE Switches & Networking', 'Cables & Conduits', 'Power Supplies & Racks', 'Access Control & Biometrics'] as $cat)
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer text-xs font-medium text-slate-800 dark:text-slate-200">
                                        <input type="checkbox" name="categories[]" value="{{ $cat }}" @checked(in_array($cat, old('categories', []))) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span>{{ $cat }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('categories') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="crm-label">Brands Supplied (Comma Separated)</label>
                            <input aria-label="Brands Supplied" type="text" name="brands_supplied" value="{{ old('brands_supplied') }}" placeholder="e.g. Hikvision, Dahua, WD Purple, Seagate, D-Link" class="crm-input">
                            @error('brands_supplied') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Vendor Notes</label>
                        <textarea aria-label="Vendor Notes" name="notes" rows="3" class="crm-input" style="height: auto;" placeholder="e.g. Delivery terms, payment schedules, contact notes">{{ old('notes') }}</textarea>
                        @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4 flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true)) class="w-4 h-4 rounded-[4px] border-[#ccc] text-blue-600">
                        <label for="is_active" class="text-sm text-gray-700">
                            Active Supplier
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
