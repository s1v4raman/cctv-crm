<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Create AMC Contract</h2>
                <p class="mt-1 text-sm text-gray-500">Register a new agreement and automatically generate scheduled maintenance visits</p>
            </div>
            <a href="{{ route('amcs.index') }}" class="btn-head-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Contracts
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
        .form-input, .form-select, .form-textarea {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 0.6rem;
            padding: 0.65rem 0.85rem; font-size: 0.875rem; color: #1e293b;
            background: #fff; outline: none; transition: all 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
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
                <form method="POST" action="{{ route('amcs.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Customer / Client Premise <span class="text-red-500">*</span></label>
                        <select name="lead_id" class="form-select" required>
                            <option value="">-- Select Customer Site --</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}" @selected(old('lead_id') == $lead->id)>
                                    {{ $lead->customer_name }} @if($lead->company_name) ({{ $lead->company_name }}) @endif {{ $lead->phone ? "— {$lead->phone}" : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('lead_id') <div class="error-msg">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Contract Start Date <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required class="form-input font-medium">
                            @error('start_date') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Contract End Date <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" value="{{ old('end_date', now()->addYear()->format('Y-m-d')) }}" required class="form-input font-medium">
                            @error('end_date') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Annual Value (₹) <span class="text-red-500">*</span></label>
                            <input type="number" name="value" min="0" step="0.01" value="{{ old('value', '0.00') }}" required class="form-input font-bold">
                            @error('value') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Servicing Frequency <span class="text-red-500">*</span></label>
                            <select name="frequency" class="form-select font-semibold" required>
                                <option value="monthly" @selected(old('frequency') === 'monthly')>Monthly (12 Visits/Year)</option>
                                <option value="quarterly" @selected(old('frequency', 'quarterly') === 'quarterly')>Quarterly (4 Visits/Year)</option>
                                <option value="semi_annually" @selected(old('frequency') === 'semi_annually')>Semi-Annually (2 Visits/Year)</option>
                                <option value="annually" @selected(old('frequency') === 'annually')>Annually (1 Visit/Year)</option>
                            </select>
                            @error('frequency') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="form-label">Contract Terms & Equipment Scope</label>
                        <textarea name="notes" rows="3" class="form-textarea" placeholder="Specify covered cameras, NVR channels, power backup checks, cleanings, and response SLA...">{{ old('notes') }}</textarea>
                        @error('notes') <div class="error-msg">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('amcs.index') }}" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Register AMC & Generate Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
