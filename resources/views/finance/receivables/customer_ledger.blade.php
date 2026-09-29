<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('finance.receivables.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                            {{ $lead->company_legal_name ?: $lead->customer_name }}
                        </h2>
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800 rounded-full">
                            Customer Ledger
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Statement of Account, chronological billing & receipt timeline, and running debtor balance
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('finance.receivables.customer.pdf', $lead->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Download Statement PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('status'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-emerald-100 dark:bg-emerald-900/80 text-emerald-700 dark:text-emerald-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-xs md:text-sm font-bold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <!-- Customer Profile & Balance Snapshot -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Customer Contact Profile Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Account Details</h3>
                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400 font-semibold">Contact Person:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $lead->customer_name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400 font-semibold">Phone:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $lead->phone ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400 font-semibold">Email:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $lead->email ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400 font-semibold">GSTIN:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $lead->gstin ?: 'Unregistered' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400 font-semibold">Address:</span>
                        <span class="text-right text-slate-700 dark:text-slate-300">{{ $lead->site_address ?: 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Total Financial Summary -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Billed</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        ₹{{ number_format($ledger['total_billed'], 2) }}
                    </div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Lifetime Invoiced Value</span>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Paid / Receipts</span>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                        ₹{{ number_format($ledger['total_paid'], 2) }}
                    </div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Collections & Settlements</span>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-indigo-200 dark:border-indigo-800/80 rounded-3xl p-5 shadow-sm">
                    <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Current Balance Due</span>
                    <div class="text-2xl font-black text-indigo-900 dark:text-indigo-100 mt-2">
                        ₹{{ number_format($ledger['outstanding_balance'], 2) }}
                    </div>
                    <span class="text-[11px] text-indigo-600/80 dark:text-indigo-300/80 mt-1 block">
                        {{ $ledger['outstanding_balance'] > 0 ? 'Pending Collection' : '✓ Zero Balance (Settled)' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Statement of Account Ledger Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Chronological Transaction Ledger</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Debit (Invoices) and Credit (Payments) with running debtor balance</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider text-[10px]">
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Type</th>
                            <th class="p-3.5">Reference #</th>
                            <th class="p-3.5">Description</th>
                            <th class="p-3.5 text-right font-bold text-slate-800 dark:text-slate-200">Debit (+)</th>
                            <th class="p-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">Credit (-)</th>
                            <th class="p-3.5 text-right font-black text-slate-900 dark:text-white">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                        @forelse($ledger['transactions'] as $tx)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="p-3.5 text-slate-600 dark:text-slate-400">
                                    {{ $tx['date']->format('d M Y') }}
                                </td>
                                <td class="p-3.5">
                                    @if($tx['type'] === 'invoice')
                                        <span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">INVOICE</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">PAYMENT</span>
                                    @endif
                                </td>
                                <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                    @if($tx['type'] === 'invoice' && isset($tx['invoice_id']))
                                        <a href="{{ route('invoices.show', $tx['invoice_id']) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $tx['reference'] }}
                                        </a>
                                    @else
                                        <span>{{ $tx['reference'] }}</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-slate-600 dark:text-slate-400 max-w-xs truncate">
                                    {{ $tx['description'] }}
                                </td>
                                <td class="p-3.5 text-right font-semibold text-slate-900 dark:text-white">
                                    {{ $tx['debit'] > 0 ? '₹' . number_format($tx['debit'], 2) : '-' }}
                                </td>
                                <td class="p-3.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ $tx['credit'] > 0 ? '₹' . number_format($tx['credit'], 2) : '-' }}
                                </td>
                                <td class="p-3.5 text-right font-black {{ $tx['balance'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                    ₹{{ number_format($tx['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">
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
