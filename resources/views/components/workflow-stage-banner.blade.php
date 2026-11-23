@props([
    'stage' => 'lead', // 'lead', 'quotation', 'project', 'jcr', 'invoice'
    'model' => null,
])

@php
    $stages = [
        'lead' => ['label' => '1. Lead', 'name' => 'Lead'],
        'quotation' => ['label' => '2. Quotation', 'name' => 'Quotation'],
        'project' => ['label' => '3. Project', 'name' => 'Project'],
        'jcr' => ['label' => '4. JCR Site Report', 'name' => 'JCR'],
        'invoice' => ['label' => '5. Invoice & Payment', 'name' => 'Invoice'],
    ];

    $stageKeys = array_keys($stages);
    $currentIndex = array_search($stage, $stageKeys);
    if ($currentIndex === false) $currentIndex = 0;

    // Resolve cross-module links
    $lead = null;
    $quotation = null;
    $project = null;
    $job = null;
    $jcr = null;
    $invoice = null;

    if ($stage === 'lead' && $model instanceof \App\Models\Lead) {
        $lead = $model;
        $quotation = $lead->quotations()->latest()->first();
        $project = $lead->projects()->latest()->first();
    } elseif ($stage === 'quotation' && $model instanceof \App\Models\Quotation) {
        $quotation = $model;
        $lead = $quotation->lead;
        $project = $quotation->project();
        $job = $quotation->installationJob;
        $jcr = $job?->jobCompletionReport;
        $invoice = $quotation->invoices()->latest()->first();
    } elseif ($stage === 'project' && $model instanceof \App\Models\Project) {
        $project = $model;
        $lead = $project->lead;
        $quotation = $lead?->quotations()->latest()->first();
    } elseif ($stage === 'invoice' && $model instanceof \App\Models\Invoice) {
        $invoice = $model;
        $quotation = $invoice->quotation;
        $lead = $quotation?->lead;
        $project = $quotation?->project();
    }
@endphp

<div class="mb-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden">
    {{-- Header Banner: Pipeline Stages --}}
    <div class="px-4 py-3 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none text-xs font-semibold">
            @foreach($stageKeys as $idx => $sKey)
                @php
                    $isPast = $idx < $currentIndex;
                    $isCurrent = $idx === $currentIndex;
                    $isFuture = $idx > $currentIndex;

                    $targetUrl = null;
                    if ($sKey === 'lead' && $lead) $targetUrl = route('leads.show', $lead);
                    elseif ($sKey === 'quotation' && $quotation) $targetUrl = route('quotations.show', $quotation);
                    elseif ($sKey === 'project' && $project) $targetUrl = route('projects.show', $project);
                    elseif ($sKey === 'jcr' && $jcr) $targetUrl = route('jcr.show', $jcr);
                    elseif ($sKey === 'invoice' && $invoice) $targetUrl = route('invoices.show', $invoice);
                @endphp

                <div class="inline-flex items-center gap-1.5 shrink-0">
                    @if($targetUrl && !$isCurrent)
                        <a href="{{ $targetUrl }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:bg-white dark:hover:bg-slate-700 transition">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold {{ $isPast ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                @if($isPast) &check; @else {{ $idx + 1 }} @endif
                            </span>
                            <span>{{ $stages[$sKey]['name'] }}</span>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $isCurrent ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-400 dark:text-slate-500' }}">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold {{ $isCurrent ? 'bg-white text-blue-600' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }}">
                                {{ $idx + 1 }}
                            </span>
                            <span>{{ $stages[$sKey]['name'] }}</span>
                        </span>
                    @endif

                    @if(!$loop->last)
                        <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-2 shrink-0 text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Stage {{ $currentIndex + 1 }} of 5:</span>
            <span class="font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ $stages[$stage]['name'] }}</span>
        </div>
    </div>

    {{-- Guided Next-Step Action Bar: Direct Jump to the Next Work Without Using Sidebar --}}
    <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900">
        <div class="flex items-center gap-2">
            <span class="flex h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
            <span class="text-xs text-slate-600 dark:text-slate-300">
                <strong>Current Step:</strong> 
                @if($stage === 'lead')
                    Customer inquiry registered ({{ ucfirst($lead->status ?? 'Active') }}).
                @elseif($stage === 'quotation')
                    Quotation {{ $quotation->quotation_no ?? '' }} (Status: <strong class="capitalize">{{ $quotation->status ?? 'Draft' }}</strong>).
                @elseif($stage === 'project')
                    Project #{{ $project->project_code ?? '' }} execution in progress.
                @elseif($stage === 'invoice')
                    Invoice #{{ $invoice->invoice_no ?? '' }} issued.
                @endif
            </span>
        </div>

        {{-- Dynamic Next Step Button --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if($stage === 'lead')
                @if($quotation)
                    <a href="{{ route('quotations.show', $quotation) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                        <span>Open Quotation #{{ $quotation->quotation_no }} &rarr;</span>
                    </a>
                @else
                    <a href="{{ route('quotations.create', $lead) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                        <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Next Step: Create Quotation &rarr;</span>
                    </a>
                @endif

                @if($project)
                    <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                        <span>View Project #{{ $project->project_code }} &rarr;</span>
                    </a>
                @else
                    <a href="{{ route('projects.create', ['lead_id' => $lead->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white shadow-xs transition">
                        <span>Direct: + Add Project</span>
                    </a>
                @endif

            @elseif($stage === 'quotation')
                @if($quotation->status === 'accepted')
                    @if($project)
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                            <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                            <span>Next Step: Work on Project #{{ $project->project_code }} &rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('projects.create', ['quotation_id' => $quotation->id, 'lead_id' => $quotation->lead_id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                            <span>Next Step: + Add Project &rarr;</span>
                        </a>
                    @endif
                @elseif(in_array($quotation->status, ['draft', 'pending_approval']))
                    <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Share quotation or mark as accepted to proceed to project creation.</span>
                @else
                    <form method="POST" action="{{ route('quotations.accept', $quotation) }}" class="inline">
                        @csrf
                        <button type="submit" data-confirm="Confirm customer accepted?" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs cursor-pointer">
                            <span>Next Step: Mark Accepted &rarr;</span>
                        </button>
                    </form>
                @endif

            @elseif($stage === 'project')
                <div class="flex items-center gap-2">
                    <a href="{{ route('workers.attendance.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition">
                        <span>Log Attendance &amp; Work</span>
                    </a>
                    <a href="{{ route('jcr.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                        <span>Complete &amp; Generate JCR &rarr;</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
