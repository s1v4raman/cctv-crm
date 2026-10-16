<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('quotations.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white mt-1">Select Customer Lead</h2>
        </div>
    </x-slot>

    <div class="max-w-[800px] mx-auto space-y-6">
        <div class="crm-section">
            <div class="pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Choose Customer</h3>
            </div>

            @if($leads->isEmpty())
                <p class="text-sm text-slate-500 my-4">You must create a customer lead first before making a quotation.</p>
                <div class="mt-4">
                    <a href="{{ route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]) }}" class="btn-primary">
                        Add New Customer
                    </a>
                </div>
            @else
                <form method="GET" id="lead-select-form">
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label for="lead_id" class="crm-label mb-0 font-semibold text-slate-800 dark:text-slate-200">
                                Customer Lead <span class="required-marker">*</span>
                            </label>
                            <a href="{{ route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]) }}" 
                               class="btn-subtle text-white font-semibold"
                               style="color: #ffffff !important;">
                                <span class="text-white font-semibold" style="color: #ffffff !important;">Add New Customer</span>
                            </a>
                        </div>
                        <select id="lead_id" name="lead_id" class="crm-select w-full" required>
                            <option value="">Select customer</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->customer_name }} ({{ $lead->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-actions mt-6">
                        <a href="{{ route('quotations.index') }}" class="btn-secondary">
                            Cancel
                        </a>
                        <button type="button" onclick="goToQuotation()" class="btn-primary">
                            Submit
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
    function goToQuotation() {
        const leadId = document.getElementById('lead_id').value;
        if (!leadId) {
            alert('Please select a lead first.');
            return;
        }
        window.location.href = `/leads/${leadId}/quote`;
    }
    </script>
</x-app-layout>
