<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Select Lead for Quotation</h2>
                <p class="mt-1 text-sm text-gray-500">Choose a customer lead to create a new price proposal</p>
            </div>
            <a href="{{ route('dashboard') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;color:#6366f1;text-decoration:none">
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
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            <div class="select-card">
                @if($leads->isEmpty())
                    <div class="empty-state">
                        <p class="mb-4">You must create a customer lead first before making a quotation.</p>
                        <a href="{{ route('leads.create') }}" class="btn-primary" style="max-width:200px;margin:0 auto">
                            + Create Lead
                        </a>
                    </div>
                @else
                    <form method="GET" id="lead-select-form">
                        <div class="form-group">
                            <label class="form-label">Select Customer Lead</label>
                            <select id="lead_id" name="lead_id" class="form-select searchable-select" required>
                                <option value="">Select a customer lead...</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}">{{ $lead->customer_name }} ({{ $lead->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" onclick="goToQuotation()" class="btn-primary">
                            Proceed to Quotation Builder →
                        </button>
                    </form>
                @endif
            </div>
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
