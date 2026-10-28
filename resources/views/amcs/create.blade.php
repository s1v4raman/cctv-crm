<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('amcs.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Create AMC Contract</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-[800px] mx-auto">

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
                <form method="POST" action="{{ route('amcs.store') }}">
                    @csrf

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Contract Agreement</h3>

                    <div class="mb-4">
                        <label class="crm-label">Customer / Client Site <span class="required-marker">*</span></label>
                        <select aria-label="Customer / Client Site" name="lead_id" class="crm-select" required>
                            <option value="">-- Select Customer Site --</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}" @selected(old('lead_id') == $lead->id)>
                                    {{ $lead->customer_name }} @if($lead->company_name) ({{ $lead->company_name }}) @endif {{ $lead->phone ? "— {$lead->phone}" : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('lead_id') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Contract Start Date <span class="required-marker">*</span></label>
                            <input aria-label="Contract Start Date" type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required class="crm-input">
                            @error('start_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">Contract End Date <span class="required-marker">*</span></label>
                            <input aria-label="Contract End Date" type="date" name="end_date" value="{{ old('end_date', now()->addYear()->format('Y-m-d')) }}" required class="crm-input">
                            @error('end_date') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Annual Value (₹) <span class="required-marker">*</span></label>
                            <input aria-label="Annual Value (₹)" type="number" name="value" min="0" step="0.01" value="{{ old('value', '0.00') }}" required class="crm-input" placeholder="e.g. 15000">
                            @error('value') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">Servicing Frequency <span class="required-marker">*</span></label>
                            <select aria-label="Servicing Frequency" name="frequency" class="crm-select" required>
                                <option value="monthly" @selected(old('frequency') === 'monthly')>Monthly (12 Visits/Year)</option>
                                <option value="quarterly" @selected(old('frequency', 'quarterly') === 'quarterly')>Quarterly (4 Visits/Year)</option>
                                <option value="semi_annually" @selected(old('frequency') === 'semi_annually')>Semi-Annually (2 Visits/Year)</option>
                                <option value="annually" @selected(old('frequency') === 'annually')>Annually (1 Visit/Year)</option>
                            </select>
                            @error('frequency') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Contract Terms &amp; Equipment Scope</label>
                        <textarea aria-label="Contract Terms &amp; Equipment Scope" name="notes" rows="3" class="crm-input" style="height: auto;" placeholder="e.g. Scope includes 8 cameras, 1 NVR, quarterly preventative maintenance">{{ old('notes') }}</textarea>
                        @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('amcs.index') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
</x-app-layout>
