<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Product</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $product->name }}</p>
            </div>
            <a href="{{ route('products.index') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;color:#6366f1;text-decoration:none">
                ← Back to Products
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:680px; margin:0 auto; padding:0 1.25rem; }

        .form-card {
            background:#fff; border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            padding:1.75rem;
        }
        .form-group { margin-bottom:1.25rem; }
        .form-label {
            display:block; font-size:.78rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:.45rem;
        }
        .form-input, .form-select, .form-textarea {
            width:100%; border:1px solid #e2e8f0; border-radius:.6rem;
            padding:.6rem .9rem; font-size:.87rem; color:#1e293b;
            background:#f8fafc; outline:none; box-sizing:border-box;
            transition:border-color .15s, background .15s; font-family:inherit;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.1);
        }
        .form-textarea { min-height:100px; resize:vertical; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        @media(max-width:480px){ .form-row { grid-template-columns:1fr; } }
        .form-error { font-size:.75rem; color:#be123c; margin-top:.35rem; }

        .toggle-wrap {
            display:flex; align-items:center; gap:.75rem; padding:.75rem 1rem;
            border-radius:.6rem; border:1px solid #e2e8f0; background:#f8fafc; cursor:pointer;
        }
        .toggle-wrap input[type=checkbox] { width:1.1rem; height:1.1rem; accent-color:#6366f1; cursor:pointer; }
        .toggle-text { font-size:.85rem; font-weight:600; color:#1e293b; }
        .toggle-sub  { font-size:.72rem; color:#94a3b8; margin-top:.1rem; }

        .price-hint {
            margin-top:.4rem; padding:.55rem .85rem;
            background:#f0fdf4; border-radius:.5rem;
            font-size:.75rem; color:#15803d; font-weight:600;
        }

        .btn-primary {
            flex:1; padding:.75rem; border-radius:.75rem;
            background:linear-gradient(135deg,#6366f1,#4f46e5);
            color:#fff; font-size:.9rem; font-weight:700;
            border:none; cursor:pointer; font-family:inherit;
            transition:opacity .15s, transform .1s;
        }
        .btn-primary:hover { opacity:.92; transform:translateY(-1px); }
        .btn-secondary {
            padding:.75rem 1.5rem; border-radius:.75rem;
            background:#f8fafc; color:#64748b; font-size:.9rem; font-weight:600;
            border:1px solid #e2e8f0; cursor:pointer; font-family:inherit;
            text-decoration:none; display:inline-flex; align-items:center;
            transition:background .15s;
        }
        .btn-secondary:hover { background:#f1f5f9; }

        .section-divider { border:none; border-top:1px solid #f1f5f9; margin:1.25rem 0; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if ($errors->any())
                <div style="margin-bottom:1.25rem;padding:.85rem 1.25rem;border-radius:.75rem;background:#fff1f2;border:1px solid #fecaca;color:#be123c;font-size:.85rem;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="form-card">
                <form method="POST" action="{{ route('products.update', $product) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group" style="grid-column:1/-1">
                            <label class="form-label">Product / Service Name <span style="color:#ef4444">*</span></label>
                            <input type="text" name="name"
                                   value="{{ old('name', $product->name) }}"
                                   class="form-input" required>
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" list="category-list" value="{{ old('category', $product->category) }}"
                                   class="form-input" placeholder="e.g. Camera, NVR/DVR, Storage HDD">
                            <datalist id="category-list">
                                <option value="Camera">
                                <option value="NVR/DVR">
                                <option value="Storage / HDD">
                                <option value="Power Supply">
                                <option value="Cabling & Wire">
                                <option value="Networking & PoE">
                                <option value="Connectors & Accessories">
                                <option value="Installation Service">
                            </datalist>
                            @error('category') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Brand / Manufacturer</label>
                            <input type="text" name="brand" list="brand-list" value="{{ old('brand', $product->brand) }}"
                                   class="form-input" placeholder="e.g. Hikvision, Dahua, CP Plus, Western Digital">
                            <datalist id="brand-list">
                                <option value="Hikvision">
                                <option value="Dahua">
                                <option value="CP Plus">
                                <option value="Western Digital">
                                <option value="Seagate">
                                <option value="Uniview">
                                <option value="TP-Link">
                                <option value="D-Link">
                            </datalist>
                            @error('brand') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Model Number</label>
                            <input type="text" name="model_no" value="{{ old('model_no', $product->model_no) }}"
                                   class="form-input" placeholder="e.g. DS-2CD2043G2-I, WD40PURZ">
                            @error('model_no') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">SKU / Product Code</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-input">
                            @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Unit <span style="color:#ef4444">*</span></label>
                            <select name="unit" class="form-select" required>
                                @foreach (['Nos', 'Mtr', 'Box', 'Set', 'Job', 'Pkt', 'Roll'] as $unit)
                                    <option value="{{ $unit }}" @selected(old('unit', $product->unit) === $unit)>{{ $unit }}</option>
                                @endforeach
                            </select>
                            @error('unit') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Warranty (Months)</label>
                            <input type="number" name="default_warranty_months" value="{{ old('default_warranty_months', $product->default_warranty_months ?: 24) }}" min="0" class="form-input">
                        </div>
                    </div>

                    <hr class="section-divider">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Current Stock Quantity</label>
                            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" class="form-input">
                            @error('stock_quantity') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Low Stock Alert Level</label>
                            <input type="number" name="min_stock_alert" value="{{ old('min_stock_alert', $product->min_stock_alert ?: 5) }}" min="0" class="form-input">
                            @error('min_stock_alert') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <hr class="section-divider">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Cost Price (₹) <span style="color:#ef4444">*</span></label>
                            <input type="number" name="cost_price" id="cost_price"
                                   value="{{ old('cost_price', $product->cost_price) }}"
                                   min="0" step="0.01" class="form-input" required oninput="calcMargin()">
                            @error('cost_price') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sale Price (₹) <span style="color:#ef4444">*</span></label>
                            <input type="number" name="unit_price" id="unit_price"
                                   value="{{ old('unit_price', $product->unit_price) }}"
                                   min="0" step="0.01" class="form-input" required oninput="calcMargin()">
                            @error('unit_price') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div id="margin-hint" class="price-hint" style="display:none"></div>

                    <hr class="section-divider">

                    <div class="form-group">
                        <label class="form-label">Description / Specification</label>
                        <textarea name="description" class="form-textarea">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <input type="hidden" name="is_active" value="0">
                        <label class="toggle-wrap">
                            <input type="checkbox" name="is_active" value="1"
                                   @checked((int)old('is_active', $product->is_active) === 1)>
                            <div>
                                <div class="toggle-text">Available for new quotations</div>
                                <div class="toggle-sub">Uncheck to hide this product from the quotation builder</div>
                            </div>
                        </label>
                    </div>

                    <div style="display:flex;gap:.75rem;margin-top:1.5rem">
                        <a href="{{ route('products.index') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function calcMargin() {
        const cost  = parseFloat(document.getElementById('cost_price').value) || 0;
        const sale  = parseFloat(document.getElementById('unit_price').value) || 0;
        const hint  = document.getElementById('margin-hint');
        if (sale > 0 && cost >= 0) {
            const profit = sale - cost;
            const pct    = ((profit / sale) * 100).toFixed(1);
            hint.style.display = 'block';
            hint.textContent   = `Margin: ₹${profit.toLocaleString('en-IN')} (${pct}%)`;
            hint.style.background = profit >= 0 ? '#f0fdf4' : '#fff1f2';
            hint.style.color      = profit >= 0 ? '#15803d' : '#be123c';
        } else {
            hint.style.display = 'none';
        }
    }
    calcMargin();
    </script>
</x-app-layout>