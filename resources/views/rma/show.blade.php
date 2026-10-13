<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('rma.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-mono">{{ $rma->rma_no }}</h2>
                    <div class="flex items-center gap-2 text-xs mt-1">
                        @php
                            $st = strtolower($rma->status ?? '');
                            $statusClass = 'status-quoted';
                            if (in_array($st, ['resolved', 'closed', 'replaced'])) $statusClass = 'status-completed';
                            elseif (in_array($st, ['rejected', 'cancelled'])) $statusClass = 'status-cancelled';
                            elseif (in_array($st, ['with_vendor', 'diagnosing', 'in_transit'])) $statusClass = 'status-progress';
                        @endphp
                        <span class="{{ $statusClass }}">
                            {{ $rma->status_label }}
                        </span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="{{ $rma->warranty_status_at_claim === 'under_warranty' ? 'text-emerald-600' : 'text-red-600' }} font-bold">
                            {{ ucwords(str_replace('_', ' ', $rma->warranty_status_at_claim)) }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('rma.dispatch-pdf', $rma) }}" class="btn-secondary">
                        Vendor Challan PDF
                    </a>
                    <a href="{{ route('rma.edit', $rma) }}" class="btn-secondary">
                        Edit
                    </a>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('rma.destroy', $rma) }}" onsubmit="return confirm('Are you sure you want to delete this RMA claim?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Section 1: Hardware Details (Two-column detail table) --}}
        <div class="crm-section">
            <h3>Faulty Hardware Details</h3>
            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">RMA Reference</td>
                        <td class="w-2/3 text-slate-900 dark:text-white font-mono">{{ $rma->rma_no }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Hardware Asset</td>
                        <td class="text-slate-900 dark:text-white">
                            {{ $rma->product?->name ?? 'Hardware Asset' }}
                            @if($rma->product?->model_no)
                                <span class="text-slate-500 text-xs">({{ $rma->product->model_no }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Faulty Serial Number</td>
                        <td class="text-slate-900 dark:text-white font-mono font-bold">{{ $rma->faulty_serial_number }}</td>
                    </tr>
                    @if($rma->faulty_mac_address)
                        <tr>
                            <td class="text-slate-500 font-bold">MAC Address</td>
                            <td class="text-slate-900 dark:text-white font-mono">{{ $rma->faulty_mac_address }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-bold">Vendor / Supplier</td>
                        <td class="text-slate-900 dark:text-white">
                            <a href="{{ route('suppliers.show', $rma->supplier) }}" class="action-link">
                                {{ $rma->supplier->name }}
                            </a>
                            @if($rma->vendor_rma_ref)
                                <span class="text-slate-500 text-xs font-mono ml-2">Vendor Ref: {{ $rma->vendor_rma_ref }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Customer Site</td>
                        <td class="text-slate-900 dark:text-white">
                            @if($rma->lead)
                                <a href="{{ route('leads.show', $rma->lead) }}" class="action-link">
                                    {{ $rma->lead->customer_name }}
                                </a>
                                @if($rma->lead->site_address)
                                    <span class="text-slate-500 text-xs">({{ $rma->lead->site_address }})</span>
                                @endif
                            @else
                                <span class="text-slate-400">Warehouse Stock</span>
                            @endif
                        </td>
                    </tr>
                    @if($rma->installedEquipment)
                        <tr>
                            <td class="text-slate-500 font-bold">Linked Installed Asset</td>
                            <td class="text-slate-900 dark:text-white">
                                <a href="{{ route('equipment.show', $rma->installedEquipment) }}" class="action-link">
                                    {{ $rma->installedEquipment->equipment_name }}
                                </a>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-bold">Fault Category</td>
                        <td class="text-slate-900 dark:text-white">{{ $rma->fault_category_label }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Symptoms Description</td>
                        <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $rma->issue_description }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Registered On</td>
                        <td class="text-slate-900 dark:text-white">{{ $rma->created_at->format('d M Y, h:i A') }} (by {{ $rma->creator->name }})</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Section 2: Shipping & Vendor Tracking --}}
        @if($rma->dispatched_date)
            <div class="crm-section">
                <h3>Shipping & Vendor Tracking</h3>
                <table class="crm-table">
                    <tbody>
                        <tr>
                            <td class="w-1/3 text-slate-500 font-bold">Courier Partner</td>
                            <td class="w-2/3 text-slate-900 dark:text-white">{{ $rma->shipping_courier }}</td>
                        </tr>
                        @if($rma->tracking_number)
                            <tr>
                                <td class="text-slate-500 font-bold">Consignment / AWB No</td>
                                <td class="text-slate-900 dark:text-white font-mono">{{ $rma->tracking_number }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-slate-500 font-bold">Dispatched Date</td>
                            <td class="text-slate-900 dark:text-white">{{ $rma->dispatched_date->format('d M Y') }}</td>
                        </tr>
                        @if($rma->turnaround_days !== null)
                            <tr>
                                <td class="text-slate-500 font-bold">Turnaround Time</td>
                                <td class="text-slate-900 dark:text-white">{{ $rma->turnaround_days }} days</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Section 3: Vendor Resolution Inward Details --}}
        @if(in_array($rma->status, ['replaced', 'repaired', 'credit_note', 'rejected', 'closed']))
            <div class="crm-section">
                <h3>Vendor Resolution Summary</h3>
                <table class="crm-table">
                    <tbody>
                        <tr>
                            <td class="w-1/3 text-slate-500 font-bold">Resolution Outcome</td>
                            <td class="w-2/3 text-slate-900 dark:text-white capitalize">{{ str_replace('_', ' ', $rma->resolution_type) }}</td>
                        </tr>
                        @if($rma->replacement_serial_number)
                            <tr>
                                <td class="text-slate-500 font-bold">New Replacement S/N</td>
                                <td class="text-slate-900 dark:text-white font-mono font-bold">{{ $rma->replacement_serial_number }}</td>
                            </tr>
                        @endif
                        @if($rma->replacement_mac_address)
                            <tr>
                                <td class="text-slate-500 font-bold">Replacement MAC</td>
                                <td class="text-slate-900 dark:text-white font-mono">{{ $rma->replacement_mac_address }}</td>
                            </tr>
                        @endif
                        @if($rma->received_from_vendor_date)
                            <tr>
                                <td class="text-slate-500 font-bold">Received Date</td>
                                <td class="text-slate-900 dark:text-white">{{ $rma->received_from_vendor_date->format('d M Y') }}</td>
                            </tr>
                        @endif
                        @if($rma->vendor_repair_notes)
                            <tr>
                                <td class="text-slate-500 font-bold">Vendor Notes</td>
                                <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $rma->vendor_repair_notes }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Action 1: Dispatch to Vendor (Draft) --}}
        @if($rma->status === 'draft')
            <div class="crm-section">
                <h3>Dispatch to Vendor</h3>
                <form method="POST" action="{{ route('rma.dispatch', $rma) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Shipping Carrier <span class="required-marker">*</span></label>
                            <input type="text" name="shipping_courier" required placeholder="e.g. DTDC, Blue Dart" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">AWB / Tracking Number</label>
                            <input type="text" name="tracking_number" placeholder="e.g. D19283741" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">Dispatched Date <span class="required-marker">*</span></label>
                            <input type="date" name="dispatched_date" value="{{ now()->format('Y-m-d') }}" required class="crm-input">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Action 2: Record Vendor Resolution --}}
        @if(in_array($rma->status, ['shipped_to_vendor', 'in_vendor_repair']))
            <div class="crm-section">
                <h3>Record Vendor Resolution</h3>
                <form method="POST" action="{{ route('rma.resolution', $rma) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Resolution Outcome <span class="required-marker">*</span></label>
                            <select name="resolution_type" required class="crm-select" onchange="toggleReplacementFields(this.value)">
                                <option value="replacement">Brand New Replacement (New S/N)</option>
                                <option value="repaired_unit">Original Unit Repaired &amp; Returned</option>
                                <option value="credit_note">Vendor Credit Note / Refund</option>
                                <option value="rejected_damage">Claim Rejected (Void)</option>
                            </select>
                        </div>
                        <div>
                            <label class="crm-label">Received Date <span class="required-marker">*</span></label>
                            <input type="date" name="received_from_vendor_date" value="{{ now()->format('Y-m-d') }}" required class="crm-input">
                        </div>
                    </div>

                    <div id="replacementSerialNumberDiv" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">New Serial Number <span class="required-marker">*</span></label>
                            <input type="text" name="replacement_serial_number" placeholder="e.g. SN-889922" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">New MAC Address</label>
                            <input type="text" name="replacement_mac_address" placeholder="e.g. 54:C8:01:B3:99:11" class="crm-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Resolution Notes</label>
                        <textarea name="vendor_repair_notes" rows="2" placeholder="e.g. Replaced motherboard and updated firmware" class="crm-input h-auto py-2"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Audit Trail Timeline Table (5.3) --}}
        <div class="crm-section">
            <h3>Audit History Log</h3>
            <table class="crm-table">
                <thead>
                    <tr>
                        <th class="nowrap">Status Transition</th>
                        <th>Notes</th>
                        <th class="nowrap">Updated By</th>
                        <th class="nowrap">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rma->statusLogs as $log)
                    <tr>
                        <td class="nowrap font-bold text-slate-900 dark:text-white capitalize">
                            {{ str_replace('_', ' ', $log->to_status) }}
                        </td>
                        <td>{{ $log->notes ?: '—' }}</td>
                        <td class="nowrap">{{ $log->changedBy->name }}</td>
                        <td class="nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-slate-500 text-sm">
                            No status history logged yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleReplacementFields(val) {
            const snDiv = document.getElementById('replacementSerialNumberDiv');
            if (snDiv) {
                snDiv.style.display = (val === 'replacement') ? 'grid' : 'none';
            }
        }
    </script>
</x-app-layout>
