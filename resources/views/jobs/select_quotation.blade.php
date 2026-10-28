<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('jobs.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white mt-1">Select Quotation for Installation Job</h2>
        </div>
    </x-slot>

    <div class="max-w-[800px] mx-auto space-y-6">
        <div class="crm-section">
            <h3>Choose Accepted Quotation</h3>

            @if($quotations->isEmpty())
                <p class="text-sm text-slate-500 my-4">No accepted quotations pending installation were found.</p>
                <p class="text-sm">
                    Please go to <a href="{{ route('quotations.index') }}" class="action-link">Quotations</a>, mark a quotation as Won/Accepted, then create an installation job.
                </p>
            @else
                <form method="POST" id="job-select-form">
                    @csrf
                    <div class="mb-4">
                        <label class="crm-label">
                            Accepted Quotation <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Accepted Quotation" name="quotation_id" id="quotation_id" class="crm-select" required>
                            <option value="">Select quotation</option>
                            @foreach($quotations as $quotation)
                                <option value="{{ $quotation->id }}">
                                    {{ $quotation->quotation_no }} - {{ $quotation->lead->customer_name }} (₹{{ number_format($quotation->total, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-actions mt-6">
                        <a href="{{ route('jobs.index') }}" class="btn-secondary">
                            Cancel
                        </a>
                        <button type="button" onclick="submitJobForm()" class="btn-primary">
                            Submit
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
    function submitJobForm() {
        const quotationId = document.getElementById('quotation_id').value;
        if (!quotationId) {
            crmAlert('Please select a quotation first.');
            return;
        }
        const form = document.getElementById('job-select-form');
        form.action = `/quotations/${quotationId}/job`;
        form.submit();
    }
    </script>
</x-app-layout>
