<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('finance.petty_cash.index') }}" class="inline-flex items-center p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight flex items-center gap-2">
                        <span>Account Statement & Running Ledger</span>
                        <span class="text-xs px-2.5 py-1 bg-indigo-100 text-indigo-800 font-semibold rounded-full uppercase tracking-wider">
                            {{ $account->account_type === 'main_vault' ? 'Head Vault' : 'Field Float' }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">Custodian: <span class="font-semibold text-gray-800">{{ $account->custodian->name ?? 'Head Office Cashier' }}</span> | Account: <span class="font-semibold text-gray-800">{{ $account->name }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('finance.petty_cash.reconcile', $account) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm rounded-xl shadow-sm transition gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Reconcile & Tally</span>
                </a>
                <a href="{{ route('finance.petty_cash.export-pdf', ['from_date' => $filters['from_date'] ?? null, 'to_date' => $filters['to_date'] ?? null]) }}" class="inline-flex items-center px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-sm rounded-xl border border-rose-200 transition gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter & Statement Summary Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-center">
                    
                    <!-- Date Filter Form -->
                    <form action="{{ route('finance.petty_cash.ledger', $account) }}" method="GET" class="lg:col-span-2 flex items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">From Date</label>
                            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">To Date</label>
                            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <button type="submit" class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-semibold rounded-xl transition">
                            Filter
                        </button>
                    </form>

                    <!-- Metrics -->
                    <div class="lg:col-span-2 grid grid-cols-3 gap-3 text-center border-t lg:border-t-0 lg:border-l border-gray-100 pt-4 lg:pt-0 lg:pl-6">
                        <div class="p-3 bg-emerald-50 rounded-xl">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Total Inflow</span>
                            <span class="text-base font-black text-emerald-800">+₹{{ number_format($ledger['total_inflow'], 2) }}</span>
                        </div>
                        <div class="p-3 bg-rose-50 rounded-xl">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 block">Total Outflow</span>
                            <span class="text-base font-black text-rose-800">-₹{{ number_format($ledger['total_outflow'], 2) }}</span>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-xl">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 block">Current Balance</span>
                            <span class="text-base font-black text-indigo-900">₹{{ number_format($ledger['current_balance'], 2) }}</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Ledger Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Chronological Transaction Log</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Wallet / Account: <span class="font-semibold text-gray-700">{{ $account->name }}</span></p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg">
                        {{ count($ledger['entries']) }} Entries
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50/75 text-[11px] font-bold uppercase text-gray-500 tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3">Date</th>
                                <th class="px-4 py-3">Voucher #</th>
                                <th class="px-4 py-3">Type & Category</th>
                                <th class="px-5 py-3">Particulars & Counterparty</th>
                                <th class="px-4 py-3 text-right">Debit (Outflow)</th>
                                <th class="px-4 py-3 text-right">Credit (Inflow)</th>
                                <th class="px-5 py-3 text-right">Running Balance</th>
                                <th class="px-4 py-3 text-center">Receipt & PDF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ledger['entries'] as $entry)
                                @php
                                    $item = $entry['transaction'];
                                @endphp
                                <tr class="hover:bg-gray-50/75 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-800 font-medium">
                                        {{ $entry['date'] }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs font-semibold text-indigo-700">
                                        {{ $entry['voucher_no'] }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            @if($item->transaction_type === 'float_advance')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">Advance</span>
                                            @elseif($item->transaction_type === 'field_collection')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Collection</span>
                                            @elseif($item->transaction_type === 'direct_expense')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700">Expense</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700">Handover</span>
                                            @endif
                                            <span class="text-xs text-gray-500">{{ $entry['category'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-gray-700">
                                        <div class="font-medium text-gray-900">{{ $entry['notes'] ?? 'Transaction' }}</div>
                                        @if($entry['payee_payer'])
                                            <div class="text-[11px] text-gray-500">Party: {{ $entry['payee_payer'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-bold text-xs whitespace-nowrap {{ $entry['outflow'] > 0 ? 'text-rose-600' : 'text-gray-300' }}">
                                        {{ $entry['outflow'] > 0 ? '₹' . number_format($entry['outflow'], 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-bold text-xs whitespace-nowrap {{ $entry['inflow'] > 0 ? 'text-emerald-600' : 'text-gray-300' }}">
                                        {{ $entry['inflow'] > 0 ? '+₹' . number_format($entry['inflow'], 2) : '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-black text-xs text-gray-900 whitespace-nowrap">
                                        ₹{{ number_format($entry['balance'], 2) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($item->receipt_photo_path)
                                                <a href="{{ asset('storage/' . $item->receipt_photo_path) }}" target="_blank" class="p-1 rounded text-gray-400 hover:text-indigo-600" title="View Uploaded Receipt">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('finance.petty_cash.voucher.pdf', $item) }}" target="_blank" class="p-1 rounded text-gray-400 hover:text-rose-600" title="Print Official Cash Voucher">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-400 text-xs">
                                        No cash transactions found for this account in the selected date range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary -->
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">Closing Current Balance</span>
                    <span class="font-black text-gray-900 text-sm">₹{{ number_format($ledger['current_balance'], 2) }}</span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
