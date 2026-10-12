<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('finance.receivables.index') }}" class="px-2.5 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded text-xs font-medium transition">
                    &larr; Back
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Customer Ledger: {{ $lead->company_legal_name ?: $lead->customer_name }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('finance.receivables.customer.pdf', $lead->id) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-[#2563eb] hover:bg-blue-700 text-white text-xs font-medium rounded transition">
                    Download Statement PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('status'))
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded text-xs font-medium text-emerald-900 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <!-- Customer Profile & Balance Snapshot -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Customer Contact Profile Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-2">
                <h2 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Account Details</h2>
                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 font-medium">Contact Person:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $lead->customer_name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 font-medium">Phone:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $lead->phone ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 font-medium">Email:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $lead->email ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 font-medium">GSTIN:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $lead->gstin ?: 'Unregistered' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500 font-medium">Address:</span>
                        <span class="text-right text-slate-700 dark:text-slate-300">{{ $lead->site_address ?: 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Total Financial Summary -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Billed</span>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                        &#8377;{{ number_format($ledger['total_billed'], 2) }}
                    </div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Lifetime Invoiced Value</span>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Paid</span>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        &#8377;{{ number_format($ledger['total_paid'], 2) }}
                    </div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Receipts &amp; Settlements</span>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Current Balance Due</span>
                    <div class="text-xl font-bold {{ $ledger['outstanding_balance'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }} mt-1">
                        &#8377;{{ number_format($ledger['outstanding_balance'], 2) }}
                    </div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                        {{ $ledger['outstanding_balance'] > 0 ? 'Pending Collection' : 'Zero Balance (Settled)' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Statement of Account Ledger Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Transaction Ledger</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="p-3">Date</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Reference #</th>
                            <th class="p-3">Description</th>
                            <th class="p-3 text-right">Debit (+)</th>
                            <th class="p-3 text-right">Credit (-)</th>
                            <th class="p-3 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($ledger['transactions'] as $tx)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="p-3 text-slate-600 dark:text-slate-400">
                                    {{ $tx['date']->format('d M Y') }}
                                </td>
                                <td class="p-3">
                                    @if($tx['type'] === 'invoice')
                                        <span class="font-bold text-blue-600 dark:text-blue-400">Invoice</span>
                                    @else
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Payment</span>
                                    @endif
                                </td>
                                <td class="p-3 font-bold text-slate-900 dark:text-white">
                                    @if($tx['type'] === 'invoice' && isset($tx['invoice_id']))
                                        <a href="{{ route('invoices.show', $tx['invoice_id']) }}" class="text-blue-600 hover:underline">
                                            {{ $tx['reference'] }}
                                        </a>
                                    @else
                                        <span>{{ $tx['reference'] }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-600 dark:text-slate-400 max-w-xs truncate">
                                    {{ $tx['description'] }}
                                </td>
                                <td class="p-3 text-right text-slate-900 dark:text-white">
                                    {{ $tx['debit'] > 0 ? '₹' . number_format($tx['debit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-right text-emerald-600 dark:text-emerald-400">
                                    {{ $tx['credit'] > 0 ? '₹' . number_format($tx['credit'], 2) : '-' }}
                                </td>
                                <td class="p-3 text-right font-bold {{ $tx['balance'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                    &#8377;{{ number_format($tx['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    No billing or payment transactions recorded for this customer yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
