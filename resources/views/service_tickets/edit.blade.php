<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('service-tickets.show', $ticket) }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white mt-1">Edit Service Ticket</h2>
        </div>
    </x-slot>

    <div class="max-w-[800px] mx-auto space-y-6 py-6 px-4 sm:px-6">
        @if ($errors->any())
            <div class="p-3 bg-white dark:bg-slate-900 border border-red-500 text-red-600 rounded text-xs">
                <p class="font-bold mb-1">Please correct the following fields:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="crm-section">
            <h3>Update Ticket Details</h3>

            <form method="POST" action="{{ route('service-tickets.update', $ticket) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Customer / Site <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Customer / Site" name="lead_id" required class="crm-select">
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}" {{ old('lead_id', $ticket->lead_id) == $lead->id ? 'selected' : '' }}>
                                    {{ $lead->customer_name }} ({{ $lead->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('lead_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="crm-label">
                            Covered Under AMC
                        </label>
                        <select aria-label="Covered Under AMC" name="amc_contract_id" class="crm-select">
                            <option value="">Standalone / No AMC</option>
                            @foreach($amcContracts as $amc)
                                <option value="{{ $amc->id }}" {{ old('amc_contract_id', $ticket->amc_contract_id) == $amc->id ? 'selected' : '' }}>
                                    {{ $amc->contract_no }} - {{ $amc->lead->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Issue Category <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Issue Category" name="issue_type" required class="crm-select">
                            @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $k => $label)
                                <option value="{{ $k }}" {{ old('issue_type', $ticket->issue_type) === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">
                            Priority <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Priority" name="priority" required class="crm-select">
                            @foreach(\App\Models\ServiceTicket::priorityOptions() as $k => $label)
                                <option value="{{ $k }}" {{ old('priority', $ticket->priority) === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">
                            Status <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Status" name="status" required class="crm-select">
                            @foreach(\App\Models\ServiceTicket::statusOptions() as $k => $label)
                                <option value="{{ $k }}" {{ old('status', $ticket->status) === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="crm-label">
                        Subject / Title <span class="required-marker">*</span>
                    </label>
                    <input aria-label="Subject / Title" type="text" name="title" value="{{ old('title', $ticket->title) }}" required class="crm-input">
                    @error('title')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="crm-label">
                        Description <span class="required-marker">*</span>
                    </label>
                    <textarea aria-label="Description" name="description" rows="3" required class="crm-input h-auto py-2">{{ old('description', $ticket->description) }}</textarea>
                    @error('description')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Assign Technician
                        </label>
                        <select aria-label="Assign Technician" name="assigned_technician_id" class="crm-select">
                            <option value="">Select technician</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ old('assigned_technician_id', $ticket->assigned_technician_id) == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">
                            Scheduled Date
                        </label>
                        <input aria-label="Scheduled Date" type="date" name="scheduled_date" value="{{ old('scheduled_date', $ticket->scheduled_date?->format('Y-m-d')) }}" class="crm-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Billing Type <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Billing Type" name="billing_type" required class="crm-select">
                            <option value="warranty_amc" {{ old('billing_type', $ticket->billing_type) === 'warranty_amc' ? 'selected' : '' }}>Under Warranty / AMC</option>
                            <option value="billable" {{ old('billing_type', $ticket->billing_type) === 'billable' ? 'selected' : '' }}>Billable</option>
                            <option value="free_courtesy" {{ old('billing_type', $ticket->billing_type) === 'free_courtesy' ? 'selected' : '' }}>Free Courtesy Visit</option>
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">
                            Charge / Amount (₹)
                        </label>
                        <input aria-label="Charge / Amount (₹)" type="number" step="0.01" name="cost" value="{{ old('cost', $ticket->cost) }}" class="crm-input">
                    </div>
                </div>

                <div class="form-actions mt-6">
                    <a href="{{ route('service-tickets.show', $ticket) }}" class="btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
