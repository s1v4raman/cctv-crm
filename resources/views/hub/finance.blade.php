<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                            Finance Analytics Hub
                        </span>
                        <span class="text-xs text-slate-400 font-medium">• {{ $totalInvoicesCount }} Total Invoices</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Finance & Accounting Analytics</h1>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #059669 !important; color: #ffffff !important; border: 1px solid #047857 !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">Invoices</span>
                </a>
                <a href="{{ route('finance.receivables.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #4f46e5 !important; color: #ffffff !important; border: 1px solid #4338ca !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">AR / Aging</span>
                </a>
                <a href="{{ route('finance.payables.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #e11d48 !important; color: #ffffff !important; border: 1px solid #be123c !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">AP / Vendor</span>
                </a>
                <a href="{{ route('finance.gst.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #d97706 !important; color: #ffffff !important; border: 1px solid #b45309 !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">GST</span>
                </a>
                <a href="{{ route('employee.hub') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-90" style="background-color: #0d9488 !important; color: #ffffff !important; border: 1px solid #0f766e !important;">
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span style="color: #ffffff !important; font-weight: 700 !important;">Workforce Hub</span>
                    <svg class="w-3.5 h-3.5" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6 pb-12">
        <x-finance-subnav />

        {{-- ═══════════════════ ROW 1: KPI TILES ═══════════════════ --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Total Invoiced --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Billed</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <div class="text-xl font-black text-slate-900 dark:text-white">₹{{ number_format($totalInvoicedAmount / 1000, 0) }}K</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $totalInvoicesCount }} invoices total</div>
            </div>

            {{-- Collected --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Collected</span>
                    <div class="w-7 h-7 rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalPaidAmount / 1000, 0) }}K</div>
                @php $collectionRate = $totalInvoicedAmount > 0 ? round($totalPaidAmount / $totalInvoicedAmount * 100) : 0; @endphp
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $collectionRate }}% collection rate</div>
            </div>

            {{-- Outstanding AR --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Outstanding</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-xl font-black text-indigo-600 dark:text-indigo-400">₹{{ number_format($totalOutstandingAR / 1000, 0) }}K</div>
                <div class="text-[10px] text-rose-500 mt-0.5 font-semibold">{{ $overdueInvoicesCount }} overdue</div>
            </div>

            {{-- Payables --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payables (AP)</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                </div>
                <div class="text-xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($totalPayablesAmount / 1000, 0) }}K</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $unpaidPoCount }} unpaid POs</div>
            </div>

            {{-- GST Liability --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">GST Liability</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                </div>
                <div class="text-xl font-black text-amber-600 dark:text-amber-400">₹{{ number_format($netGstLiability / 1000, 0) }}K</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Net GST payable</div>
            </div>

            {{-- Petty Cash --}}
            <div class="col-span-1 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Petty Cash</span>
                    <div class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
                <div class="text-xl font-black text-sky-600 dark:text-sky-400">₹{{ number_format($totalPettyCashBalance, 0) }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $activePettyAccountsCount }} float registers</div>
            </div>
        </div>

        {{-- ═══════════════════ ROW 2: REVENUE TREND + STATUS BREAKDOWN ═══════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Invoice Status Breakdown --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Invoice Status</h3>
                        <p class="text-[11px] text-slate-400">Collection pipeline overview</p>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="text-[10px] font-bold text-emerald-600 hover:underline">View All →</a>
                </div>
                @php
                    $total = max(1, $totalInvoicesCount);
                    $paidPct    = round($invoiceStatusBreakdown['paid'] / $total * 100);
                    $unpaidPct  = round($invoiceStatusBreakdown['unpaid'] / $total * 100);
                    $partPct    = round($invoiceStatusBreakdown['partially_paid'] / $total * 100);
                    $draftPct   = round($invoiceStatusBreakdown['draft'] / $total * 100);
                @endphp
                <div class="flex rounded-full overflow-hidden h-4 mb-4 gap-px">
                    @if($invoiceStatusBreakdown['paid'])    <div class="bg-emerald-500" style="width:{{ $paidPct }}%"></div> @endif
                    @if($invoiceStatusBreakdown['partially_paid']) <div class="bg-sky-400" style="width:{{ $partPct }}%"></div> @endif
                    @if($invoiceStatusBreakdown['unpaid'])  <div class="bg-rose-500" style="width:{{ $unpaidPct }}%"></div> @endif
                    @if($invoiceStatusBreakdown['draft'])   <div class="bg-slate-300 dark:bg-slate-700 flex-1"></div> @endif
                </div>
                <div class="space-y-2">
                    @foreach([
                        ['Paid',            $invoiceStatusBreakdown['paid'],           'bg-emerald-500', 'text-emerald-600'],
                        ['Partially Paid',  $invoiceStatusBreakdown['partially_paid'], 'bg-sky-400',     'text-sky-600'],
                        ['Unpaid',          $invoiceStatusBreakdown['unpaid'],          'bg-rose-500',    'text-rose-600'],
                        ['Overdue',         $invoiceStatusBreakdown['overdue'],         'bg-orange-500',  'text-orange-600'],
                        ['Draft',           $invoiceStatusBreakdown['draft'],           'bg-slate-300',   'text-slate-500'],
                    ] as [$label, $count, $dot, $text])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $dot }}"></span>
                            <span class="text-xs text-slate-600 dark:text-slate-300">{{ $label }}</span>
                        </div>
                        <span class="text-xs font-bold {{ $text }}">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 6-Month Revenue vs Collection Chart --}}
            <div class="lg:col-span-2 p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">6-Month Revenue Trend</h3>
                        <p class="text-[11px] text-slate-400">Invoiced vs Collected per month</p>
                    </div>
                    <div class="flex items-center gap-3 text-[10px] font-semibold">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Invoiced</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Collected</span>
                    </div>
                </div>
                @php $maxRev = max(1, $revenueTrend->max(fn($r) => max($r['invoiced'], $r['collected']))); @endphp
                <div class="flex items-end gap-3 h-36">
                    @foreach($revenueTrend as $r)
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        <div class="w-full flex gap-1 items-end" style="height:120px">
                            <div class="flex-1 rounded-t bg-blue-500/70 transition-all"
                                 style="height:{{ $maxRev > 0 ? max(2, round($r['invoiced'] / $maxRev * 112)) : 2 }}px"
                                 title="Invoiced: ₹{{ number_format($r['invoiced'], 0) }}"></div>
                            <div class="flex-1 rounded-t bg-emerald-500/70 transition-all"
                                 style="height:{{ $maxRev > 0 ? max(2, round($r['collected'] / $maxRev * 112)) : 2 }}px"
                                 title="Collected: ₹{{ number_format($r['collected'], 0) }}"></div>
                        </div>
                        <span class="text-[9px] text-slate-400 font-medium">{{ $r['month'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══════════════════ ROW 3: AR AGING + GST BREAKDOWN ═══════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- AR Aging Buckets --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">AR Aging Buckets</h3>
                        <p class="text-[11px] text-slate-400">Outstanding by overdue days</p>
                    </div>
                    <a href="{{ route('finance.receivables.index') }}" class="text-[10px] font-bold text-indigo-600 hover:underline">View AR →</a>
                </div>
                @php $maxAging = max(1, max($arAging)); @endphp
                @foreach([
                    ['0–30 days',  $arAging['0-30'],  'bg-emerald-500', 'text-emerald-600'],
                    ['31–60 days', $arAging['31-60'], 'bg-amber-400',   'text-amber-600'],
                    ['61–90 days', $arAging['61-90'], 'bg-orange-500',  'text-orange-600'],
                    ['90+ days',   $arAging['90+'],   'bg-rose-600',    'text-rose-600'],
                ] as [$label, $amt, $barColor, $textColor])
                @php $pct = $maxAging > 0 ? round($amt / $maxAging * 100) : 0; @endphp
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $label }}</span>
                        <span class="text-xs font-bold {{ $textColor }}">₹{{ number_format($amt, 0) }}</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-700" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between">
                    <span class="text-xs text-slate-500 font-medium">Total Outstanding</span>
                    <span class="text-xs font-black text-indigo-600">₹{{ number_format($totalOutstandingAR, 0) }}</span>
                </div>
            </div>

            {{-- GST Summary + Job Costing --}}
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- GST Card --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">GST Overview</h3>
                            <p class="text-[11px] text-slate-400">Output tax vs ITC credit</p>
                        </div>
                        <a href="{{ route('finance.gst.index') }}" class="text-[10px] font-bold text-amber-600 hover:underline">GST Hub →</a>
                    </div>
                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <div class="text-[10px] text-slate-400 font-medium mb-1">Output GST (Sales)</div>
                            <div class="text-lg font-black text-slate-900 dark:text-white">₹{{ number_format($totalOutputGst, 0) }}</div>
                        </div>
                        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30">
                            <div class="text-[10px] text-emerald-600 font-medium mb-1">Input Tax Credit (ITC)</div>
                            <div class="text-lg font-black text-emerald-600">₹{{ number_format($totalInputGst, 0) }}</div>
                        </div>
                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30">
                            <div class="text-[10px] text-amber-600 font-medium mb-1">Net GST Payable</div>
                            <div class="text-lg font-black text-amber-600">₹{{ number_format($netGstLiability, 0) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Job Costing + AP Card --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Jobs & Payables</h3>
                        <p class="text-[11px] text-slate-400">Project P&L and vendor AP</p>
                    </div>
                    @php $completionPct = $jobsCount > 0 ? round($completedJobsCount / $jobsCount * 100) : 0; @endphp
                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-teal-50 dark:bg-teal-950/30">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-[10px] text-teal-600 font-bold">Job Completion Rate</span>
                                <span class="text-xs font-black text-teal-700">{{ $completionPct }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-teal-200/60 dark:bg-teal-900/50 rounded-full overflow-hidden">
                                <div class="bg-teal-500 h-full rounded-full" style="width:{{ $completionPct }}%"></div>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ $completedJobsCount }} / {{ $jobsCount }} CCTV Jobs</div>
                        </div>
                        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30">
                            <div class="text-[10px] text-rose-600 font-medium mb-1">Vendor Payables (AP)</div>
                            <div class="text-lg font-black text-rose-600">₹{{ number_format($totalPayablesAmount, 0) }}</div>
                            <div class="text-[10px] text-slate-400">{{ $unpaidPoCount }} unpaid · {{ $threeWayMatchedCount }} 3-way matched</div>
                        </div>
                        <a href="{{ route('finance.job-costing.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors border border-dashed border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">View Job P&L →</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════ ROW 4: RECENT INVOICES TABLE ═══════════════════ --}}
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Invoices</h3>
                    <p class="text-[11px] text-slate-400">Latest 8 invoices · sorted by date</p>
                </div>
                <a href="{{ route('invoices.index') }}" class="text-[10px] font-bold text-emerald-600 hover:underline">View All Invoices →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="text-left px-5 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Invoice #</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Customer</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Date</th>
                            <th class="text-left px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="text-right px-4 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total</th>
                            <th class="text-right px-5 py-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Balance Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentInvoices as $inv)
                        @php
                            $statusStyles = match($inv->status) {
                                'paid'           => ['bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400', 'Paid'],
                                'partially_paid' => ['bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-400', 'Part Paid'],
                                'unpaid'         => ['bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400', 'Unpaid'],
                                'draft'          => ['bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400', 'Draft'],
                                default          => ['bg-amber-100 text-amber-800', ucfirst($inv->status)],
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="font-mono font-bold text-blue-600 hover:underline text-[11px]">
                                    {{ $inv->invoice_no ?? '#' . str_pad($inv->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-900 dark:text-white font-medium">
                                {{ $inv->customer_name }}
                            </td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-[10px]">
                                {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusStyles[0] }}">{{ $statusStyles[1] }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                                ₹{{ number_format($inv->total, 0) }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if(($inv->balance_due ?? 0) > 0)
                                    <span class="font-bold text-rose-600">₹{{ number_format($inv->balance_due, 0) }}</span>
                                @else
                                    <span class="text-emerald-600 font-bold">Cleared</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-10 text-slate-400 text-xs">No invoices found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════ ROW 5: TOP UNPAID + QUICK ACTIONS ═══════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Top Unpaid Receivables --}}
            <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Top Unpaid Receivables</h3>
                        <p class="text-[11px] text-slate-400">Highest outstanding balances due</p>
                    </div>
                    <a href="{{ route('finance.receivables.index') }}" class="text-[10px] font-bold text-indigo-600 hover:underline">Full AR →</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($topUnpaid as $inv)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">
                                {{ strtoupper(substr($inv->customer_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-900 dark:text-white">
                                    {{ $inv->customer_name }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {{ $inv->invoice_no ?? '#' . str_pad($inv->id, 5, '0', STR_PAD_LEFT) }}
                                    · Due {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M') : '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-black text-rose-600">₹{{ number_format($inv->balance_due ?? 0, 0) }}</div>
                            @if($inv->due_date && \Carbon\Carbon::parse($inv->due_date)->isPast())
                                <div class="text-[10px] font-semibold text-rose-500">Overdue</div>
                            @else
                                <div class="text-[10px] text-slate-400">Pending</div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs">All invoices cleared!</span>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Action Panel --}}
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Quick Actions</h3>
                    <p class="text-[11px] text-slate-400">Finance management shortcuts</p>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div><div class="text-xs font-bold text-slate-900 dark:text-white">Invoices</div><div class="text-[10px] text-slate-400">{{ $totalInvoicesCount }} total bills</div></div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('finance.receivables.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div><div class="text-xs font-bold text-slate-900 dark:text-white">AR Collections</div><div class="text-[10px] text-rose-500">{{ $overdueInvoicesCount }} overdue bills</div></div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('finance.payables.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div><div class="text-xs font-bold text-slate-900 dark:text-white">Vendor Payables</div><div class="text-[10px] text-slate-400">{{ $unpaidPoCount }} unpaid POs</div></div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('finance.gst.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-amber-50 dark:hover:bg-amber-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        </div>
                        <div><div class="text-xs font-bold text-slate-900 dark:text-white">GST Filing</div><div class="text-[10px] text-slate-400">Net: ₹{{ number_format($netGstLiability, 0) }}</div></div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('finance.petty_cash.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-sky-50 dark:hover:bg-sky-950/30 group transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-950/50 text-sky-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div><div class="text-xs font-bold text-slate-900 dark:text-white">Petty Cash</div><div class="text-[10px] text-slate-400">₹{{ number_format($totalPettyCashBalance, 0) }} float</div></div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
