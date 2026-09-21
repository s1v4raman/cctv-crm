<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Generate Invoice — {{ $job->job_no }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Customer: <strong class="text-gray-800">{{ $job->quotation->lead->customer_name }}</strong> &middot; 
                    Quotation: <strong class="text-gray-800">{{ $job->quotation->quotation_no }}</strong>
                </p>
            </div>
            <a href="{{ route('jobs.show', $job) }}" class="btn-head-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Job
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:680px; margin:0 auto; padding:0 1.25rem; }

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

        .info-strip {
            background:#eff6ff; border:1px solid #bfdbfe; border-radius:.75rem;
            padding:1rem 1.25rem; font-size:.875rem; color:#1e40af; margin-bottom:1.5rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        .error-msg { color: #dc2626; font-size: 0.78rem; margin-top: 0.35rem; font-weight: 600; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            
            <div class="info-strip">
                <div>
                    Quotation Subtotal: <strong>₹{{ number_format($job->quotation->subtotal, 2) }}</strong>
                </div>
                <div>
                    Quotation Total: <strong class="text-base text-indigo-900">₹{{ number_format($job->quotation->total, 2) }}</strong>
                </div>
            </div>

            <div class="pg-card">
                <form method="POST" action="{{ route('invoices.store', $job) }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Invoice Date <span class="text-red-500">*</span></label>
                            <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required class="form-input font-medium">
                            @error('invoice_date') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" class="form-input font-medium">
                            @error('due_date') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Discount Amount (₹)</label>
                            <input type="number" name="discount" min="0" step="0.01" value="{{ old('discount', $job->quotation->discount) }}" class="form-input font-semibold">
                            @error('discount') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">GST Tax Percent (%)</label>
                            <input type="number" name="tax_percent" min="0" max="100" step="0.01" value="{{ old('tax_percent', $job->quotation->tax_percent) }}" class="form-input font-semibold">
                            @error('tax_percent') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="form-label">Payment Terms & Bank Instructions</label>
                        <textarea name="notes" rows="3" class="form-textarea" placeholder="Enter bank account details, UPI ID, or terms for the customer...">{{ old('notes') }}</textarea>
                        @error('notes') <div class="error-msg">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('jobs.show', $job) }}" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Generate Tax Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
