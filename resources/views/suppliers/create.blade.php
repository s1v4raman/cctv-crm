<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-gray-900">Register CCTV Hardware Supplier</h2>
            </div>
            <a href="{{ route('suppliers.index') }}" class="btn-head-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Suppliers
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:780px; margin:0 auto; padding:0 1.25rem; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.5rem 1rem; border-radius: 4px;
            font-size: 0.875rem; font-weight: 500; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }

        .pg-card {
            background:#fff;
            border-radius:4px;
            border:1px solid #e2e8f0;
            box-shadow:none;
            padding: 2rem;
        }

        .form-label {
            display: block; font-size: 14px; font-weight: 500;
            text-transform: none;
            color: #475569; margin-bottom: 0.35rem;
        }
        .form-input, .form-textarea {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 4px;
            padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #1e293b;
            background: #fff; outline: none; min-height: 38px;
        }
        .form-input:focus, .form-textarea:focus {
            border-color: #2563eb; box-shadow: none;
        }

        .btn-submit-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.5rem 1.25rem; border-radius: 4px;
            font-size: 0.875rem; font-weight: 600; min-height: 38px;
            background-color: #2563eb; color: #ffffff !important;
            border: 1px solid #1d4ed8; cursor: pointer;
            box-shadow: none;
            transition: background-color 0.15s;
        }
        .btn-submit-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-cancel {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.5rem 1rem; border-radius: 4px;
            font-size: 0.875rem; font-weight: 500; min-height: 38px;
            background-color: #ffffff; color: #475569 !important;
            border: 1px solid #cbd5e1; text-decoration: none;
        }
        .btn-cancel:hover { background-color: #f8fafc; color: #0f172a !important; }

        .error-msg { color: #dc2626; font-size: 0.78rem; margin-top: 0.35rem; font-weight: 500; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-[4px] bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pg-card">
                <form method="POST" action="{{ route('suppliers.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Supplier / Business Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Hikvision India Central, Dahua Tech Hub" required class="form-input font-bold">
                            @error('name') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Legal / Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Prama Hikvision Pvt Ltd" class="form-input">
                            @error('company_name') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Ramesh Sharma" class="form-input">
                            @error('contact_person') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Phone / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. +91 98765 43210" class="form-input">
                            @error('phone') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. orders@supplier.com" class="form-input">
                            @error('email') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">GSTIN / Tax ID</label>
                            <input type="text" name="gst_number" value="{{ old('gst_number') }}" placeholder="e.g. 27AAAAA0000A1Z5" class="form-input font-mono">
                            @error('gst_number') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Payment Terms</label>
                            <input type="text" name="payment_terms" value="{{ old('payment_terms', 'Net 30 Days') }}" placeholder="e.g. 100% Advance, Net 30, Net 15" class="form-input">
                            @error('payment_terms') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="sm:col-span-1">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="Street, Industrial Area" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">City</label>
                            <input type="text" name="city" value="{{ old('city') }}" placeholder="e.g. Mumbai, Bengaluru" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">State</label>
                            <input type="text" name="state" value="{{ old('state') }}" placeholder="e.g. Maharashtra, Karnataka" class="form-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Vendor Notes & Bank Details</label>
                        <textarea name="notes" rows="2" class="form-textarea" placeholder="Account numbers, distributor discount tier, authorized contact details...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-6 flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true)) class="w-4 h-4 rounded-[4px] border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">
                            Active Supplier (Available for Purchase Orders)
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('suppliers.index') }}" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit-primary">
                            Register Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
