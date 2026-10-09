<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Delivery Challans &bull; Ready to Invoice</h2>
                    <span class="text-xs font-bold uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2.5 py-1 rounded border border-amber-200 dark:border-amber-500/30">Billing Queue</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Reconcile delivered materials: unbilled Delivery Challans awaiting formal customer tax invoices</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold text-xs border border-amber-300 dark:border-amber-800">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>{{ $totalUninvoicedCount }} Uninvoiced Challans</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6"
         x-data="{
             selectedDcs: [],
             invoiceModalOpen: false,
             selectAll(groupItems) {
                 groupItems.forEach(id => {
                     if (!this.selectedDcs.includes(id)) {
                         this.selectedDcs.push(id);
                     }
                 });
             },
             clearGroup(groupItems) {
                 this.selectedDcs = this.selectedDcs.filter(id => !groupItems.includes(id));
             },
             openInvoiceModal(singleId = null) {
                 if (singleId) {
                     this.selectedDcs = [singleId];
                 }
                 if (this.selectedDcs.length === 0) {
                     alert('Please select at least one Delivery Challan to mark as invoiced.');
                     return;
                 }
                 this.invoiceModalOpen = true;
             }
         }">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        {{-- Filter & Search Bar --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('documents.to-invoice') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto flex-1">
                <div class="relative w-full sm:w-80">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search DC number, project, site..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="company" onchange="this.form.submit()" class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 outline-none">
                        <option value="all">All Executing Companies</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ $companyFilter == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn-secondary text-xs py-2 px-3">Filter</button>
                    @if($search || $companyFilter !== 'all')
                        <a href="{{ route('documents.to-invoice') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
                    @endif
                </div>
            </form>

            <button type="button" @click="openInvoiceModal()" :disabled="selectedDcs.length === 0" class="btn-primary text-xs shadow-md flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Mark Selected as Invoiced (<span x-text="selectedDcs.length"></span>)</span>
            </button>
        </div>

        {{-- Grouped DC Listings by Company --}}
        @if($groupedByCompany->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 text-slate-400">
                <div class="text-4xl mb-2">🎉</div>
                <h3 class="font-bold text-slate-700 dark:text-slate-200 text-base">All Caught Up!</h3>
                <p class="text-xs mt-1">There are no pending Delivery Challans waiting to be invoiced.</p>
            </div>
        @else
            @foreach($groupedByCompany as $companyName => $docs)
                @php
                    $docIds = $docs->pluck('id')->toArray();
                @endphp
                <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm font-heading">{{ $companyName }}</h3>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ $docs->count() }} DCs
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" @click="selectAll({{ json_encode($docIds) }})" class="text-blue-600 hover:underline">Select All</button>
                            <span class="text-slate-300">&bull;</span>
                            <button type="button" @click="clearGroup({{ json_encode($docIds) }})" class="text-slate-400 hover:underline">Deselect</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="bg-white dark:bg-[#0f172a] text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th class="px-5 py-3 w-10"></th>
                                    <th class="px-5 py-3">DC Number &amp; Date</th>
                                    <th class="px-5 py-3">Site / Client Name</th>
                                    <th class="px-5 py-3">Project</th>
                                    <th class="px-5 py-3">Items Summary</th>
                                    <th class="px-5 py-3">Uploaded By</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($docs as $doc)
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-5 py-3.5">
                                            <input type="checkbox" :value="{{ $doc->id }}" x-model="selectedDcs" class="rounded text-blue-600 focus:ring-blue-500">
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="font-bold font-mono text-slate-900 dark:text-white text-sm">
                                                {{ $doc->dc_number ?? 'DC-N/A' }}
                                            </div>
                                            <div class="text-xs text-slate-400">
                                                {{ $doc->dc_date ? $doc->dc_date->format('d M Y') : 'No date' }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                                {{ $doc->project->site?->name ?? $doc->project->company_name }}
                                            </div>
                                            <div class="text-xs text-slate-400 truncate max-w-xs">
                                                {{ $doc->project->site?->address ?? $doc->project->site_address }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <a href="{{ route('projects.show', $doc->project) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $doc->project->project_code }}
                                            </a>
                                            <div class="text-xs text-slate-400 truncate max-w-xs">{{ $doc->project->title }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 max-w-xs text-xs">
                                            {{ $doc->items_summary ?? $doc->title }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-slate-400">
                                            {{ $doc->uploader?->name ?? 'System' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('projects.documents.view', $doc) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Document">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                                <button type="button" @click="openInvoiceModal({{ $doc->id }})" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 transition">
                                                    Mark Invoiced
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Mark Invoiced Modal --}}
        <div x-show="invoiceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="invoiceModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Mark Delivery Challans as Invoiced</h3>
                    <button type="button" @click="invoiceModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('documents.mark-invoiced') }}" class="space-y-4">
                    @csrf
                    <template x-for="id in selectedDcs" :key="id">
                        <input type="hidden" name="document_ids[]" :value="id">
                    </template>

                    <div>
                        <span class="text-xs text-slate-400">Selected Challans</span>
                        <div class="font-bold text-slate-900 dark:text-white text-sm"><span x-text="selectedDcs.length"></span> Delivery Challan(s) will be updated</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Invoice Number *</label>
                        <input type="text" name="invoice_number" required placeholder="e.g. INV-2026-0042 or PITS/26/089" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Invoice Date *</label>
                        <input type="date" name="invoiced_at" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="invoiceModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Confirm Invoice Link</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
