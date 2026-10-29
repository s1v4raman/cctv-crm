<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white">Finance</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $totalInvoicesCount }} {{ Str::plural('invoice', $totalInvoicesCount) }} · {{ \App\Support\Inr::format($totalOutstandingAR) }} still to collect</p>
        </div>
    </x-slot>

    <div class="space-y-6 pb-12">
        <x-finance-subnav />

        {{-- Headline figures: what we billed, what came in, what is still owed each way --}}
        @php $office = auth()->user()->isOffice(); @endphp
        @php $collectionRate = $totalInvoicedAmount > 0 ? round($totalPaidAmount / $totalInvoicedAmount * 100) : 0; @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">Billed</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ \App\Support\Inr::format($totalInvoicedAmount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $totalInvoicesCount }} {{ Str::plural('invoice', $totalInvoicesCount) }}</div>
            </div>
            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">Collected</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ \App\Support\Inr::format($totalPaidAmount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $collectionRate }}% of what was billed</div>
            </div>
            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">Still to collect</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ \App\Support\Inr::format($totalOutstandingAR) }}</div>
                <div class="text-xs mt-1 {{ $overdueInvoicesCount > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }}">
                    {{ $overdueInvoicesCount > 0 ? $overdueInvoicesCount . ' ' . Str::plural('invoice', $overdueInvoicesCount) . ' overdue' : 'nothing overdue' }}
                </div>
            </div>
            <div class="p-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">We owe suppliers</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ \App\Support\Inr::format($totalPayablesAmount) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $unpaidPoCount }} unpaid {{ Str::plural('purchase order', $unpaidPoCount) }}</div>
            </div>
        </div>

        {{-- Invoices by status and the last six months --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Invoices by status</h3>
                    <a href="{{ route('invoices.index') }}" class="text-xs action-link">All invoices</a>
                </div>
                <dl class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @foreach([
                        ['Paid',           $invoiceStatusBreakdown['paid'],           'bg-emerald-500'],
                        ['Partly paid',    $invoiceStatusBreakdown['partially_paid'], 'bg-blue-400'],
                        ['Unpaid',         $invoiceStatusBreakdown['unpaid'],         'bg-slate-400'],
                        ['Overdue',        $invoiceStatusBreakdown['overdue'],        'bg-amber-500'],
                        ['Draft',          $invoiceStatusBreakdown['draft'],          'bg-slate-200 dark:bg-slate-600'],
                    ] as [$label, $count, $dot])
                    <div class="flex items-center justify-between py-2">
                        <dt class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><span class="w-2 h-2 rounded-full {{ $dot }}"></span>{{ $label }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $count }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>

            <div class="lg:col-span-2 p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Billed and collected, last 6 months</h3>
                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Billed</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-400"></span>Collected</span>
                    </div>
                </div>
                @php $maxRev = max(1, $revenueTrend->max(fn ($r) => max($r['invoiced'], $r['collected']))); @endphp
                <div class="flex items-end gap-3 h-36">
                    @foreach($revenueTrend as $r)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full flex gap-1 items-end" style="height:120px"
                             title="{{ $r['month'] }}: billed {{ \App\Support\Inr::format($r['invoiced']) }}, collected {{ \App\Support\Inr::format($r['collected']) }}">
                            <div class="flex-1 rounded-t bg-blue-500" style="height:{{ max(2, round($r['invoiced'] / $maxRev * 112)) }}px"></div>
                            <div class="flex-1 rounded-t bg-slate-400 dark:bg-slate-500" style="height:{{ max(2, round($r['collected'] / $maxRev * 112)) }}px"></div>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $r['month'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Money owed to us by age, GST, and cash and jobs --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Owed to us, by age</h3>
                    @if($office)<a href="{{ route('finance.receivables.index') }}" class="text-xs action-link">Receivables</a>@endif
                </div>
                @php $maxAging = max(1, max($arAging)); @endphp
                <dl class="space-y-3 text-sm">
                    @foreach([
                        ['0–30 days',  $arAging['0-30']],
                        ['31–60 days', $arAging['31-60']],
                        ['61–90 days', $arAging['61-90']],
                        ['Over 90 days', $arAging['90+']],
                    ] as $i => [$label, $amt])
                    <div>
                        <div class="flex justify-between">
                            <dt class="text-slate-600 dark:text-slate-300">{{ $label }}</dt>
                            <dd class="font-medium {{ $i >= 2 && $amt > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">{{ \App\Support\Inr::format($amt) }}</dd>
                        </div>
                        <div class="mt-1 h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="bg-slate-400 dark:bg-slate-500 h-full" style="width:{{ round($amt / $maxAging * 100) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                    <div class="flex justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-600 dark:text-slate-300">Total</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">{{ \App\Support\Inr::format($totalOutstandingAR) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-baseline justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">GST</h3>
                    @if($office)<a href="{{ route('finance.gst.index') }}" class="text-xs action-link">GST returns</a>@endif
                </div>
                <dl class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-600 dark:text-slate-300">Collected on sales</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ \App\Support\Inr::format($totalOutputGst) }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-600 dark:text-slate-300">Paid on purchases (credit)</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">− {{ \App\Support\Inr::format($totalInputGst) }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="font-medium text-slate-900 dark:text-white">To pay</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">{{ \App\Support\Inr::format($netGstLiability) }}</dd>
                    </div>
                </dl>
            </div>

            @php $completionPct = $jobsCount > 0 ? round($completedJobsCount / $jobsCount * 100) : 0; @endphp
            <div class="p-5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Cash and jobs</h3>
                <dl class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-600 dark:text-slate-300">Petty cash in hand</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ \App\Support\Inr::format($totalPettyCashBalance) }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-600 dark:text-slate-300">Cash registers</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $activePettyAccountsCount }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-600 dark:text-slate-300">Jobs finished</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $completedJobsCount }} of {{ $jobsCount }} <span class="text-xs font-normal text-slate-400">· {{ $completionPct }}%</span></dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-600 dark:text-slate-300">Bills matched to POs</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ $threeWayMatchedCount }}</dd>
                    </div>
                </dl>
                @if($office)
                <div class="mt-3 flex gap-4 text-xs">
                    <a href="{{ route('finance.petty_cash.index') }}" class="action-link">Petty cash</a>
                    <a href="{{ route('finance.job-costing.index') }}" class="action-link">Job costing</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Latest invoices and the biggest unpaid ones --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="flex items-baseline justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Recent invoices</h3>
                    <a href="{{ route('invoices.index') }}" class="text-xs action-link">All invoices</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                <th class="px-5 py-2 font-medium">Invoice</th>
                                <th class="px-4 py-2 font-medium">Customer</th>
                                <th class="px-4 py-2 font-medium">Date</th>
                                <th class="px-4 py-2 font-medium">Status</th>
                                <th class="px-4 py-2 font-medium text-right">Total</th>
                                <th class="px-5 py-2 font-medium text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($recentInvoices as $inv)
                            @php
                                $statusLabel = match($inv->status) {
                                    'paid' => 'Paid', 'partially_paid' => 'Partly paid', 'unpaid' => 'Unpaid', 'draft' => 'Draft',
                                    default => ucfirst(str_replace('_', ' ', $inv->status)),
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-2.5 whitespace-nowrap"><a href="{{ route('invoices.show', $inv->id) }}">{{ $inv->invoice_no ?? '#' . str_pad($inv->id, 5, '0', STR_PAD_LEFT) }}</a></td>
                                <td class="px-4 py-2.5 text-slate-900 dark:text-white">{{ $inv->customer_name }}</td>
                                <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('j M Y') }}</td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $statusLabel }}</td>
                                <td class="px-4 py-2.5 text-right text-slate-900 dark:text-white whitespace-nowrap">{{ \App\Support\Inr::format($inv->total) }}</td>
                                <td class="px-5 py-2.5 text-right whitespace-nowrap {{ ($inv->balance_due ?? 0) > 0 ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400' }}">
                                    {{ ($inv->balance_due ?? 0) > 0 ? \App\Support\Inr::format($inv->balance_due) : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-10 text-sm text-slate-500 dark:text-slate-400">No invoices yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="flex items-baseline justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Largest unpaid</h3>
                    @if($office)<a href="{{ route('finance.receivables.index') }}" class="text-xs action-link">Receivables</a>@endif
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($topUnpaid as $inv)
                    @php $isOverdue = $inv->due_date && \Carbon\Carbon::parse($inv->due_date)->isPast(); @endphp
                    <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm">
                        <div class="min-w-0">
                            <div class="text-slate-900 dark:text-white truncate">{{ $inv->customer_name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $inv->invoice_no ?? '#' . str_pad($inv->id, 5, '0', STR_PAD_LEFT) }}
                                @if($inv->due_date) · <span class="{{ $isOverdue ? 'text-amber-700 dark:text-amber-400' : '' }}">{{ $isOverdue ? 'was due' : 'due' }} {{ \Carbon\Carbon::parse($inv->due_date)->format('j M') }}</span> @endif
                            </div>
                        </div>
                        <div class="font-medium text-slate-900 dark:text-white whitespace-nowrap">{{ \App\Support\Inr::format($inv->balance_due ?? 0) }}</div>
                    </li>
                    @empty
                    <li class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Nothing unpaid.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
