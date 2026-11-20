<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">
                    Goods Receipt Note: Inward Stock from PO #{{ $purchaseOrder->po_number }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Record delivered hardware, rejected items, serial numbers, and update the inventory ledger.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn-secondary">
                    &larr; Back to PO
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
                <strong class="font-bold">Please correct the following errors:</strong>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('goods-receipts.store') }}">
            @csrf
            <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

            {{-- Receipt Metadata --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm mb-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Consignment & Delivery Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Supplier / Vendor
                        </label>
                        <input type="text" readonly value="{{ $purchaseOrder->supplier?->company_name ?: ($purchaseOrder->supplier?->name ?? 'Vendor') }}" class="crm-input w-full bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Delivery Note / DC Number
                        </label>
                        <input type="text" name="delivery_note_no" value="{{ old('delivery_note_no') }}" placeholder="e.g. DC-2026-998" class="crm-input w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Received Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required class="crm-input w-full">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Receiving Inspection Remarks / Notes
                        </label>
                        <textarea name="notes" rows="2" placeholder="Inspection notes, packaging condition, delivery comments..." class="crm-input w-full">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">PO Item Verification & Inwarding</h3>
                        <p class="text-xs text-slate-500">Specify accepted and rejected units. Items with serial tracking require 1 serial number per accepted unit.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    @foreach($purchaseOrder->items as $item)
                        @php
                            $pending = $item->pendingQuantity();
                            $trackSerial = (bool) ($item->product?->track_serial ?? false);
                        @endphp
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base">
                                        {{ $item->item_name }}
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5">
                                        @if($item->sku)<span>SKU: <strong class="font-mono">{{ $item->sku }}</strong></span>@endif
                                        <span>Ordered: <strong>{{ $item->quantity_ordered }}</strong></span>
                                        <span>Received: <strong>{{ $item->quantity_received }}</strong></span>
                                        <span>Pending: <strong class="text-blue-600">{{ $pending }}</strong></span>
                                    </div>
                                </div>
                                <div>
                                    @if($trackSerial)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200">
                                            Serial Tracking Enabled
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-emerald-700 dark:text-emerald-400 mb-1">
                                        Accepted Qty (Stock In)
                                    </label>
                                    <input type="number" name="received[{{ $item->id }}]" min="0" value="{{ old("received.{$item->id}", $pending > 0 ? $pending : 0) }}" class="crm-input w-full font-bold text-emerald-700 dark:text-emerald-400">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-rose-700 dark:text-rose-400 mb-1">
                                        Rejected Qty (Damaged / Faulty)
                                    </label>
                                    <input type="number" name="rejected[{{ $item->id }}]" min="0" value="{{ old("rejected.{$item->id}", 0) }}" class="crm-input w-full font-bold text-rose-700 dark:text-rose-400">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Rejection Reason (if any)
                                    </label>
                                    <input type="text" name="reject_reason[{{ $item->id }}]" value="{{ old("reject_reason.{$item->id}") }}" placeholder="Damaged package, physical crack..." class="crm-input w-full text-xs">
                                </div>
                            </div>

                            @if($trackSerial)
                                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                                    <label class="block text-xs font-semibold text-indigo-700 dark:text-indigo-400 mb-1">
                                        Serial Numbers (Enter 1 serial per line or comma-separated; must match accepted count)
                                    </label>
                                    <textarea name="serials[{{ $item->id }}]" rows="3" placeholder="SN1001&#10;SN1002&#10;SN1003" class="crm-input w-full font-mono text-xs">{{ old("serials.{$item->id}") }}</textarea>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Inward Stock & Generate GRN
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
