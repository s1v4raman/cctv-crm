<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Petty cash</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.petty_cash.export-csv') }}" class="btn-secondary">
                    Export CSV
                </a>
                <a href="{{ route('finance.petty_cash.export-pdf') }}" class="btn-secondary">
                    Cashbook PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6" x-data="{
        advanceModal: false,
        collectionModal: false,
        expenseModal: false,
        handoverModal: false,
        selectedAccount: null,
        selectedAccountId: '',
        selectedCustodianId: '',
        selectedAccountName: ''
    }">

        {{-- Finance Category Sub-Navigation --}}
        <x-finance-subnav active="petty_cash" />

        <!-- Status Alerts -->
        @if(session('status') || session('success'))
            <div class="p-3 rounded border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-900 dark:text-emerald-200 text-xs font-medium">
                {{ session('status') ?: session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 rounded border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/50 text-rose-900 dark:text-rose-200 text-xs font-medium space-y-1">
                <div class="font-bold">Please correct the errors below:</div>
                <ul class="list-disc list-inside pl-1 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Action Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded">
            <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Quick Actions</span>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="advanceModal = true" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded transition">
                    Issue Float Advance
                </button>
                <button type="button" @click="collectionModal = true" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 font-medium text-xs rounded transition">
                    Record Cash Collection
                </button>
                <button type="button" @click="expenseModal = true" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 font-medium text-xs rounded transition">
                    Log Petty Expense
                </button>
                <button type="button" @click="handoverModal = true" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 font-medium text-xs rounded transition">
                    Handover / Deposit
                </button>
            </div>
        </div>

        <!-- Top KPI Overview Metrics -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Main Safe Vault</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                    &#8377;{{ number_format($summary['main_vault_balance'], 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Field Custodian Floats</div>
                <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">
                    &#8377;{{ number_format($summary['total_field_float'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $summary['active_custodians_count'] }} technician wallets
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Liquid Cash</div>
                <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">
                    &#8377;{{ number_format($summary['total_liquid_cash'], 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Day Collections</div>
                <div class="text-xl font-semibold text-slate-900 dark:text-white mt-1">
                    &#8377;{{ number_format($summary['daily_collections'], 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">High Float Risk</div>
                <div class="text-xl font-bold {{ $summary['high_risk_wallets_count'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }} mt-1">
                    {{ $summary['high_risk_wallets_count'] }} <span class="text-xs font-normal text-slate-400">Wallets</span>
                </div>
            </div>
        </div>

        <!-- Custodian Wallets Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Cash Float Custodians & Wallets</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="p-3">Account / Custodian</th>
                            <th class="p-3">Role</th>
                            <th class="p-3 text-right">Float Limit</th>
                            <th class="p-3 text-right">Current In-Hand</th>
                            <th class="p-3 text-center">Float Risk</th>
                            <th class="p-3">Last Activity</th>
                            <th class="p-3 text-center">Daily Tally</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($accounts as $acc)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $acc['name'] }}
                                    </div>
                                    @if($acc['custodian'])
                                        <div class="text-[11px] text-slate-400 font-normal">{{ $acc['custodian']->email }}</div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    {{ $acc['custodian_role'] }}
                                </td>
                                <td class="p-3 text-right text-slate-500">
                                    &#8377;{{ number_format($acc['warning_limit'], 2) }}
                                </td>
                                <td class="p-3 text-right font-bold text-sm {{ $acc['is_high_risk'] ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                    &#8377;{{ number_format($acc['current_balance'], 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($acc['is_high_risk'])
                                        <span class="font-bold text-rose-600 dark:text-rose-400">High Risk</span>
                                    @else
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Normal</span>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-600 dark:text-slate-400">
                                    {{ $acc['latest_tx_date'] }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($acc['is_reconciled_today'])
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Tally Done</span>
                                    @else
                                        <span class="font-bold text-amber-600 dark:text-amber-400">Pending Tally</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('finance.petty_cash.reconcile', $acc['id']) }}" class="text-blue-600 hover:underline">Reconcile</a>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <a href="{{ route('finance.petty_cash.ledger', $acc['id']) }}" class="text-blue-600 hover:underline">Ledger</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    No petty cash accounts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions Feed & Reconciliations Split -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Recent Transactions -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Recent Cash Transactions & Vouchers</h2>
                    <span class="text-xs text-slate-400">Latest 25 entries</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                            <tr>
                                <th class="p-3">Voucher #</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Account</th>
                                <th class="p-3">Type / Category</th>
                                <th class="p-3 text-right">Amount</th>
                                <th class="p-3 text-center">Voucher PDF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($recentTransactions as $tx)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    <td class="p-3 font-bold text-slate-900 dark:text-white">
                                        {{ $tx->voucher_no }}
                                    </td>
                                    <td class="p-3 text-slate-500">
                                        {{ $tx->transaction_date->format('d M Y') }}
                                    </td>
                                    <td class="p-3 font-medium text-slate-800 dark:text-slate-200">
                                        {{ $tx->account?->name ?? 'Vault' }}
                                    </td>
                                    <td class="p-3">
                                        @if($tx->transaction_type === 'float_advance')
                                            <span class="font-bold text-blue-600 dark:text-blue-400">Advance Float</span>
                                        @elseif($tx->transaction_type === 'field_collection')
                                            <span class="font-bold text-teal-600 dark:text-teal-400">Customer Cash</span>
                                        @elseif($tx->transaction_type === 'direct_expense')
                                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ ucfirst(str_replace('_', ' ', $tx->category)) }}</span>
                                        @else
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Handover / Deposit</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right font-bold text-slate-900 dark:text-white">
                                        &#8377;{{ number_format($tx->amount, 2) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('finance.petty_cash.voucher.pdf', $tx->id) }}" class="text-blue-600 hover:underline">
                                            PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400">
                                        No recent transactions recorded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Col: Daily Reconciliations Log -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Recent Daily Tallies</h2>
                    <span class="text-xs text-slate-400">Audit logs</span>
                </div>

                <div class="space-y-3">
                    @forelse($recentReconciliations as $rec)
                        <div class="p-3 rounded border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $rec->reconciliation_no }}</span>
                                <span class="text-[11px] text-slate-400">{{ $rec->reconciliation_date->format('d M Y') }}</span>
                            </div>
                            <div class="text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                {{ $rec->account?->name }}
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200 dark:border-slate-700">
                                <div>
                                    <span class="text-slate-400 text-[11px]">Physical: </span>
                                    <span class="font-bold text-slate-900 dark:text-white">&#8377;{{ number_format($rec->physical_counted_balance, 2) }}</span>
                                </div>
                                <div>
                                    @if($rec->variance_status === 'matched')
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">0.00 Matched</span>
                                    @elseif($rec->variance_status === 'shortage')
                                        <span class="font-bold text-rose-600 dark:text-rose-400">-&#8377;{{ number_format(abs($rec->variance_amount), 2) }} Short</span>
                                    @else
                                        <span class="font-bold text-amber-600 dark:text-amber-400">+&#8377;{{ number_format($rec->variance_amount, 2) }} Excess</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">
                            No recent reconciliations recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 1: Issue Float Advance (Main Vault -> Technician Wallet) -->
        <!-- ========================================================================= -->
        <div x-show="advanceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;" @keydown.escape.window="advanceModal = false">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded max-w-lg w-full p-6 space-y-4" @click.away="advanceModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Issue Cash Float Advance</h3>
                    <button aria-label="Close" @click="advanceModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('finance.petty_cash.advance') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Field Custodian *</label>
                        <select aria-label="Field Custodian" name="custodian_id" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                            <option value="">-- Select Field Custodian --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }} - {{ $tech->phone ?? $tech->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Advance Amount (&#8377;) *</label>
                            <input aria-label="Advance Amount (&#8377;)" type="number" step="1" min="1" name="amount" required placeholder="5000" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Disbursement Date *</label>
                            <input aria-label="Disbursement Date" type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Purpose / Notes</label>
                        <textarea aria-label="Purpose / Notes" name="notes" rows="2" placeholder="Weekly field cash float" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="advanceModal = false" class="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium text-xs rounded transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded transition">
                            Disburse Float
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 2: Record Customer Cash Collection -->
        <!-- ========================================================================= -->
        <div x-show="collectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;" @keydown.escape.window="collectionModal = false">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded max-w-lg w-full p-6 space-y-4" @click.away="collectionModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Record Customer Cash Collection</h3>
                    <button aria-label="Close" @click="collectionModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('finance.petty_cash.collection') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Receiving Custodian *</label>
                        <select aria-label="Receiving Custodian" name="custodian_id" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount Collected (&#8377;) *</label>
                            <input aria-label="Amount Collected (&#8377;)" type="number" step="1" min="1" name="amount" required placeholder="3500" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Collection Date *</label>
                            <input aria-label="Collection Date" type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Customer / Client Name *</label>
                        <input aria-label="Customer / Client Name" type="text" name="customer_name" required placeholder="Customer Name" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Link to Invoice</label>
                            <select aria-label="Link to Invoice" name="invoice_id" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                                <option value="">-- No Specific Invoice --</option>
                                @foreach($pendingInvoices as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->invoice_no }} (&#8377;{{ number_format($inv->balanceDue(), 2) }} due - {{ $inv->quotation?->lead?->customer_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Receipt / Slip #</label>
                            <input aria-label="Receipt / Slip #" type="text" name="receipt_reference" placeholder="SLIP-1092" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="collectionModal = false" class="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium text-xs rounded transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded transition">
                            Save Collection
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 3: Log Localized Petty Expense -->
        <!-- ========================================================================= -->
        <div x-show="expenseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;" @keydown.escape.window="expenseModal = false">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded max-w-lg w-full p-6 space-y-4" @click.away="expenseModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Log Site Petty Expense</h3>
                    <button aria-label="Close" @click="expenseModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('finance.petty_cash.expense') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Paying Account / Wallet *</label>
                        <select aria-label="Paying Account / Wallet" name="petty_cash_account_id" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc['id'] }}">{{ $acc['name'] }} (Balance: &#8377;{{ number_format($acc['current_balance'], 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expense Category *</label>
                            <select aria-label="Expense Category" name="category" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                                <option value="hardware_conduits">Hardware, PVC Pipes & Conduits</option>
                                <option value="materials">Screws, Fasteners & Cable Clips</option>
                                <option value="travel_fuel">Vehicle Petrol / Conveyance</option>
                                <option value="food_refreshment">Site Labor Refreshment / Tea</option>
                                <option value="tolls_parking">Toll Plaza & Parking Fees</option>
                                <option value="office_supplies">Office Stationeries & Staples</option>
                                <option value="other">Miscellaneous Site Expense</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expense Amount (&#8377;) *</label>
                            <input aria-label="Expense Amount (&#8377;)" type="number" step="0.50" min="0.50" name="amount" required placeholder="450" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor / Payee Name</label>
                            <input aria-label="Vendor / Payee Name" type="text" name="vendor_payee_name" placeholder="Vendor Name" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Date of Purchase *</label>
                            <input aria-label="Date of Purchase" type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Attach Job</label>
                            <select aria-label="Attach Job" name="job_id" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                                <option value="">-- General Site Expense --</option>
                                @foreach($activeJobs as $j)
                                    <option value="{{ $j->id }}">{{ $j->job_no }} ({{ $j->lead?->customer_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="f-receipt-photo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Attach Receipt</label>
                            <input id="f-receipt-photo" type="file" name="receipt_photo" accept="image/*,.pdf" class="w-full text-xs text-slate-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description / Remarks</label>
                        <textarea aria-label="Description / Remarks" name="notes" rows="2" placeholder="Description of items purchased" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="expenseModal = false" class="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium text-xs rounded transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded transition">
                            Record Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 4: Cash Handover & Deposit -->
        <!-- ========================================================================= -->
        <div x-show="handoverModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;" @keydown.escape.window="handoverModal = false">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded max-w-lg w-full p-6 space-y-4" @click.away="handoverModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Cash Handover / Bank Deposit</h3>
                    <button aria-label="Close" @click="handoverModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('finance.petty_cash.handover') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Source Field Wallet *</label>
                        <select aria-label="Source Field Wallet" name="source_account_id" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                            @foreach($accounts as $acc)
                                @if($acc['account_type'] === 'field_wallet')
                                    <option value="{{ $acc['id'] }}">{{ $acc['name'] }} (In-Hand: &#8377;{{ number_format($acc['current_balance'], 2) }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Destination *</label>
                            <select aria-label="Destination" name="destination_type" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                                <option value="main_vault">Head Office Safe Vault</option>
                                <option value="bank_deposit">Company Current Bank Account</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Handover Amount (&#8377;) *</label>
                            <input aria-label="Handover Amount (&#8377;)" type="number" step="1" min="1" name="amount" required placeholder="10000" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Handover Date *</label>
                            <input aria-label="Handover Date" type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bank / Vault Ack Ref #</label>
                            <input aria-label="Bank / Vault Ack Ref #" type="text" name="bank_ack_no" placeholder="DEPOSIT-SLIP-8821" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Handover Remarks</label>
                        <textarea aria-label="Handover Remarks" name="notes" rows="2" placeholder="Remarks" class="w-full px-3 py-2 text-sm rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="handoverModal = false" class="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium text-xs rounded transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded transition">
                            Process Handover
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
