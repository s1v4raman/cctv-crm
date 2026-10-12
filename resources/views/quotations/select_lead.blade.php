<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Select Lead for Quotation</h2>
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-xl mx-auto">
        <div class="bg-white dark:bg-[#0f172a] rounded border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
            @if($leads->isEmpty())
                <div class="text-center py-6 text-sm text-slate-500">
                    <p class="mb-4">You must create a customer lead first before making a quotation.</p>
                    <a href="{{ route('leads.create') }}" class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        + Create Lead
                    </a>
                </div>
            @else
                <form method="GET" id="lead-select-form">
                    <div class="mb-5">
                        <label for="lead_id" class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Select Customer Lead <span class="text-red-500">*</span>
                        </label>
                        <select id="lead_id" name="lead_id" data-no-tom class="w-full h-10 px-3 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" required>
                            <option value="">Select a customer lead...</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->customer_name }} ({{ $lead->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" onclick="goToQuotation()" class="w-full h-10 px-4 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition flex items-center justify-center">
                        Proceed to Quotation Builder →
                    </button>
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
