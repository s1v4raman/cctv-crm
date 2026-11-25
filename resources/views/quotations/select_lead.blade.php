<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ auth()->user()->isTechnician() ? route('technician.dashboard') : route('quotations.index') }}" class="back-link">&larr; Back</a>
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
                    @if(auth()->user()->isInternal())
                    <a href="{{ route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]) }}" class="btn-primary">
                        <span>Add New Customer</span>
                    </a>
                    @endif
                </div>
            @else
                <form method="GET" id="lead-select-form">
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label for="lead_id" class="crm-label mb-0 font-semibold text-slate-800 dark:text-slate-200">
                                Customer Lead <span class="required-marker">*</span>
                            </label>
                            @if(auth()->user()->isInternal())
                            <a href="{{ route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]) }}" 
                               class="btn-subtle gap-1.5 text-white font-semibold"
                               style="color: #ffffff !important;">
                                <span class="text-white font-semibold" style="color: #ffffff !important;">Add New Customer</span>
                            </a>
                            @endif
                        </div>
                        <select id="lead_id" name="lead_id" class="crm-select w-full" required>
                            <option value="">Select customer</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->customer_name }} ({{ $lead->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-actions mt-6 flex items-center justify-end gap-3">
                        <a href="{{ auth()->user()->isTechnician() ? route('technician.dashboard') : route('quotations.index') }}" 
                           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 font-semibold text-xs sm:text-sm transition shadow-2xs">
                            {{ __('Cancel') }}
                        </a>
                        <button type="button" onclick="goToQuotation()" 
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer"
                                style="color: #ffffff !important; background-color: #2563eb !important;">
                            <span class="text-white font-bold" style="color: #ffffff !important;">{{ __('Proceed to Quotation') }}</span>
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
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
            crmAlert('Please select a lead first.');
            return;
        }
        window.location.href = `/leads/${leadId}/quote`;
    }
    </script>
</x-app-layout>
