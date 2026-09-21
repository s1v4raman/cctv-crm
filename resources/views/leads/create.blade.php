<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">New Lead</h2>
                <p class="mt-1 text-sm text-gray-500">Add a new customer enquiry to the pipeline</p>
            </div>
            <a href="{{ route('leads.index') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;color:#6366f1;text-decoration:none">
                ← Back to Leads
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:640px; margin:0 auto; padding:0 1.25rem; }

        .form-card {
            background:#fff;
            border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            padding:1.75rem;
        }

        .form-group { margin-bottom:1.25rem; }
        .form-label {
            display:block; font-size:.78rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.05em;
            color:#64748b; margin-bottom:.45rem;
        }
        .form-input, .form-select, .form-textarea {
            width:100%; border:1px solid #e2e8f0; border-radius:.6rem;
            padding:.6rem .9rem; font-size:.87rem; color:#1e293b;
            background:#f8fafc; outline:none; box-sizing:border-box;
            transition:border-color .15s, background .15s;
            font-family:inherit;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color:#6366f1; background:#fff;
            box-shadow:0 0 0 3px rgba(99,102,241,.1);
        }
        .form-textarea { min-height:90px; resize:vertical; }

        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        @media(max-width:480px){ .form-row { grid-template-columns:1fr; } }

        .form-error { font-size:.75rem; color:#be123c; margin-top:.35rem; }

        .btn-primary {
            width:100%; padding:.75rem; border-radius:.75rem;
            background:linear-gradient(135deg,#6366f1,#4f46e5);
            color:#fff; font-size:.9rem; font-weight:700;
            border:none; cursor:pointer; letter-spacing:.01em;
            transition:opacity .15s, transform .1s;
        }
        .btn-primary:hover { opacity:.92; transform:translateY(-1px); }
        .btn-primary:active { transform:translateY(0); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if ($errors->any())
                <div style="margin-bottom:1rem;padding:.85rem 1.25rem;border-radius:.75rem;background:#fff1f2;border:1px solid #fecaca;color:#be123c;font-size:.85rem;margin-bottom:1.25rem">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="form-card">
                <form method="POST" action="{{ route('leads.store') }}">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Customer Name <span style="color:#ef4444">*</span></label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                   class="form-input" placeholder="e.g. Raman Kumar" required>
                            @error('customer_name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone <span style="color:#ef4444">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="form-input" placeholder="10-digit mobile" required>
                            @error('phone')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="form-input" placeholder="Optional">
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-select">
                                <option value="">Select source…</option>
                                <option value="referral"  @selected(old('source') === 'referral')>Referral</option>
                                <option value="walk-in"   @selected(old('source') === 'walk-in')>Walk-in</option>
                                <option value="call"      @selected(old('source') === 'call')>Phone Call</option>
                                <option value="website"   @selected(old('source') === 'website')>Website</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Site Address</label>
                        <textarea name="site_address" class="form-textarea" placeholder="Installation site address…">{{ old('site_address') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-textarea" placeholder="Any additional notes or requirements…">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn-primary">Create Lead</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>