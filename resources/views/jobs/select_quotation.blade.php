<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white font-heading">Select Quotation for Installation Job</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Choose an accepted price proposal to initiate its installation job</p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="text-indigo-600 dark:text-indigo-400 hover:underline"
               style="display:inline-flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;text-decoration:none">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:680px; margin:0 auto; padding:0 1.25rem; }

        .select-card {
            background:#fff; border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            padding:1.75rem;
        }

        .form-group { margin-bottom:1.5rem; }
        .form-label {
            display:block; font-size:.78rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:.45rem;
        }
        .form-select {
            width:100%; border:1px solid #e2e8f0; border-radius:.6rem;
            padding:.6rem .9rem; font-size:.87rem; color:#1e293b;
            background:#f8fafc; outline:none; box-sizing:border-box;
            transition:border-color .15s, background .15s; font-family:inherit;
        }
        .form-select:focus {
            border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.1);
        }

        .btn-primary {
            width:100%; padding:.75rem; border-radius:.75rem;
            background:linear-gradient(135deg,#6366f1,#4f46e5);
            color:#fff; font-size:.9rem; font-weight:700;
            border:none; cursor:pointer; font-family:inherit;
            transition:opacity .15s, transform .1s; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
        }
        .btn-primary:hover { opacity:.92; transform:translateY(-1px); }

        .empty-state { text-align:center; padding:2rem 1rem; color:#94a3b8; font-size:.85rem; }
        .empty-state a { color:#6366f1; font-weight:600; text-decoration:none; }

        /* Dark mode overrides */
        html.dark .pg-wrap { background: #060913; }
        html.dark .select-card { background: #0f172a; border-color: #1e293b; color: #f8fafc; }
        html.dark .form-label { color: #cbd5e1; }
        html.dark .form-select { background: #0b1120; border-color: #334155; color: #f8fafc; }
        html.dark .empty-state { color: #cbd5e1; }
        html.dark .empty-state a { color: #818cf8; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            <div class="select-card">
                @if($quotations->isEmpty())
                    <div class="empty-state">
                        <p class="mb-4">No accepted quotations pending installation were found.</p>
                        <p class="text-sm">Please go to <a href="{{ route('quotations.index') }}">Quotations</a>, mark a quotation as <strong>Accepted</strong>, and then create a job.</p>
                    </div>
                @else
                    <form method="POST" id="job-select-form">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Select Accepted Quotation</label>
                            <select name="quotation_id" id="quotation_id" class="form-select" required>
                                <option value="">Select an accepted quotation...</option>
                                @foreach($quotations as $quotation)
                                    <option value="{{ $quotation->id }}">
                                        {{ $quotation->quotation_no }} &middot; {{ $quotation->lead->customer_name }} (₹{{ number_format($quotation->total, 0) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" onclick="submitJobForm()" class="btn-primary">
                            Create Installation Job →
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
    function submitJobForm() {
        const quotationId = document.getElementById('quotation_id').value;
        if (!quotationId) {
            alert('Please select a quotation first.');
            return;
        }
        const form = document.getElementById('job-select-form');
        form.action = `/quotations/${quotationId}/job`;
        form.submit();
    }
    </script>
</x-app-layout>
