<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Supplier — {{ $supplier->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">Update vendor contact details and procurement terms</p>
            </div>
            <a href="{{ route('suppliers.show', $supplier) }}" class="btn-head-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Supplier Profile
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:780px; margin:0 auto; padding:0 1.25rem; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }

        .pg-card {
            background:#fff;
            border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            padding: 2rem;
        }

        .form-label {
            display: block; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
            color: #475569; margin-bottom: 0.4rem;
        }
        .form-input, .form-textarea {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 0.6rem;
            padding: 0.65rem 0.85rem; font-size: 0.875rem; color: #1e293b;
            background: #fff; outline: none; transition: all 0.15s;
        }
        .form-input:focus, .form-textarea:focus {
            border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-submit-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.65rem 1.5rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 700;
            background-color: #4f46e5; color: #ffffff !important;
            border: 1px solid #4338ca; cursor: pointer;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
            transition: all 0.15s ease-in-out;
        }
        .btn-submit-primary:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.35);
        }

        .btn-cancel {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.65rem 1.25rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600;
            background-color: #ffffff; color: #475569 !important;
            border: 1px solid #cbd5e1; text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-cancel:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }

        .error-msg { color: #dc2626; font-size: 0.78rem; margin-top: 0.35rem; font-weight: 600; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            <div class="pg-card">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Supplier / Business Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="form-input font-bold">
                            @error('name') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Legal / Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" class="form-input">
                            @error('company_name') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="form-input">
                            @error('contact_person') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Phone / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-input">
                            @error('phone') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-input">
                            @error('email') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">GSTIN / Tax ID</label>
                            <input type="text" name="gst_number" value="{{ old('gst_number', $supplier->gst_number) }}" class="form-input font-mono">
                            @error('gst_number') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label">Payment Terms</label>
                            <input type="text" name="payment_terms" value="{{ old('payment_terms', $supplier->payment_terms) }}" class="form-input">
                            @error('payment_terms') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="form-label">Address</label>
                            <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">City</label>
                            <input type="text" name="city" value="{{ old('city', $supplier->city) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">State</label>
                            <input type="text" name="state" value="{{ old('state', $supplier->state) }}" class="form-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Vendor Notes & Bank Details</label>
                        <textarea name="notes" rows="2" class="form-textarea">{{ old('notes', $supplier->notes) }}</textarea>
                    </div>

                    <div class="mb-6 flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $supplier->is_active)) class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <label for="is_active" class="text-xs font-bold text-gray-800 cursor-pointer">
                            Active Supplier (Available for Purchase Orders)
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit-primary">
                            💾 Save Supplier Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
