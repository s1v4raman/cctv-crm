<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Generate Invoice</h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-[800px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('jobs.show', $job) }}" class="back-link">&larr; Back</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Generate Invoice &mdash; {{ $job->job_no }}</h2>
            </div>

            <div class="crm-section mb-4">
                <table class="w-full text-sm">
                    <tr>
                        <td class="w-1/3 text-gray-500 font-bold py-1">Customer</td>
                        <td class="text-gray-900 py-1 font-bold">{{ $job->quotation->lead->customer_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-500 font-bold py-1">Quotation Number</td>
                        <td class="text-gray-900 py-1">{{ $job->quotation->quotation_no }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-500 font-bold py-1">Quotation Subtotal</td>
                        <td class="text-gray-900 py-1">₹{{ number_format($job->quotation->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-500 font-bold py-1">Quotation Total</td>
                        <td class="text-gray-900 py-1 font-bold">₹{{ number_format($job->quotation->total, 2) }}</td>
                    </tr>
                </table>
            </div>

            <div class="crm-section">
                <form method="POST" action="{{ route('invoices.store', $job) }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Invoice Date <span class="required-marker">*</span></label>
                            <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required class="crm-input">
                            @error('invoice_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" class="crm-input">
                            @error('due_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Discount Amount (₹)</label>
                            <input type="number" name="discount" min="0" step="0.01" value="{{ old('discount', $job->quotation->discount) }}" class="crm-input" placeholder="e.g. 500">
                            @error('discount') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">GST Tax Percent (%)</label>
                            <input type="number" name="tax_percent" min="0" max="100" step="0.01" value="{{ old('tax_percent', $job->quotation->tax_percent) }}" class="crm-input" placeholder="e.g. 18">
                            @error('tax_percent') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Payment Terms & Bank Instructions</label>
                        <textarea name="notes" rows="3" class="crm-input" style="height: auto;" placeholder="e.g. Payment due in 7 days via NEFT/UPI">{{ old('notes') }}</textarea>
                        @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('jobs.show', $job) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
