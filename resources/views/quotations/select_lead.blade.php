<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('quotations.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white mt-1">Select Customer Lead</h2>
        </div>
    </x-slot>

    <div class="max-w-[800px] mx-auto space-y-6">
        <div class="crm-section">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Choose Customer</h3>
                <a href="{{ route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 border border-blue-200 dark:border-blue-800 transition shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add New Customer</span>
                </a>
            </div>

            @if($leads->isEmpty())
                <p class="text-sm text-slate-500 my-4">You must create a customer lead first before making a quotation.</p>
                <div class="mt-4">
                    <a href="{{ route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]) }}" class="btn-primary">
                        + Add New Customer
                    </a>
                </div>
            @else
                <form method="GET" id="lead-select-form">
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="lead_id" class="crm-label mb-0">
                                Customer Lead <span class="required-marker">*</span>
                            </label>
                            <a href="{{ route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]) }}" 
                               class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 inline-flex items-center gap-1 hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>+ Add New Customer</span>
                            </a>
                        </div>
                        <select id="lead_id" name="lead_id" class="crm-select" required>
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
