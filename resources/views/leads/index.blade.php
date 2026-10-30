<x-app-layout>
    @php
        $isOffice = auth()->user()->isOffice();
        $isAdmin = auth()->user()->isAdmin();
        $viewLabels = \App\Http\Controllers\LeadController::VIEWS;
        $quickFilters = \App\Http\Controllers\LeadController::QUICK_FILTERS;
        $sortLabels = \App\Http\Controllers\LeadController::SORTS;
        $statusLabels = ['new' => 'New', 'contacted' => 'Contacted', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost'];
        $only = (array) request('only', []);
        $pickedStatus = (array) request('status', []);
        $pickedSource = (array) request('source', []);
        $letter = preg_match('/^[A-Z]$/', (string) request('letter')) ? request('letter') : null;
        $filterCount = count($only) + count($pickedStatus) + count($pickedSource) + (request()->filled('search') ? 1 : 0);
        $here = fn (array $change) => request()->fullUrlWithQuery(array_merge(['page' => null], $change));
    @endphp

    <x-list-page-assets />

    <div class="ld-root" x-data="listPage(@js($leads->pluck('id')), 'leads', { company: true, email: true, phone: true, source: true, status: true, created: true }, @js($filterCount > 0))" @keydown.escape.window="menu = null">

        @if (session('status'))
            <div class="px-4 py-2 text-sm bg-emerald-50 text-emerald-800 border-b border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900">{{ session('status') }}</div>
        @endif

        <div class="ld-band">
            {{-- Saved views --}}
            <div class="ld-views">
                <span class="ld-chip">{{ $viewLabels[$view] }}</span>
                <div class="relative" @click.outside="menu === 'views' && (menu = null)">
                    <button type="button" class="ld-icon-btn" title="Other views" aria-label="Other views" :aria-expanded="menu === 'views'" @click="open('views')">
                        <svg class="icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                    </button>
                    <div class="ld-menu left-0" x-show="menu === 'views'" x-cloak>
                        @foreach($viewLabels as $key => $label)
                            <a href="{{ route('leads.index', array_filter(['view' => $key === 'all' ? null : $key, 'layout' => $layout === 'board' ? 'board' : null])) }}" class="plain-link {{ $view === $key ? 'is-current' : '' }}">
                                <span>{{ $label }}</span>
                                @if($view === $key)<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Tools, or what to do with the ticked leads --}}
            <div class="ld-toolbar">
                <div class="flex items-center gap-1" x-show="selected.length === 0">
                    <button type="button" class="ld-tool" :class="showFilter && 'is-on'" @click="showFilter = !showFilter; $nextTick(() => fit())" :aria-pressed="showFilter">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18l-7 8.5V19l-4 2v-7.5L3 5z"/></svg>
                        <span>Filter</span>
                        @if($filterCount > 0)<span class="text-xs font-semibold" style="color: var(--ld-accent)">{{ $filterCount }}</span>@endif
                    </button>

                    <div class="relative" @click.outside="menu === 'sort' && (menu = null)">
                        <button type="button" class="ld-tool" :aria-expanded="menu === 'sort'" @click="open('sort')">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4v16m0 0l-3-3m3 3l3-3M16 20V4m0 0l-3 3m3-3l3 3"/></svg>
                            <span>Sort</span>
                        </button>
                        <div class="ld-menu left-0" x-show="menu === 'sort'" x-cloak>
                            @foreach($sortLabels as $key => $label)
                                <a href="{{ $here(['sort' => $key === 'date_desc' ? null : $key]) }}" class="plain-link {{ $sort === $key ? 'is-current' : '' }}">
                                    <span>{{ $label }}</span>
                                    @if($sort === $key)<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <span class="ld-sep" aria-hidden="true"></span>

                    <a href="{{ $here(['layout' => null]) }}" class="ld-icon-btn plain-link {{ $layout === 'list' ? 'is-on' : '' }}" title="List" aria-label="List view" @if($layout === 'list') aria-current="page" @endif>
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6h11M9 12h11M9 18h11"/><circle cx="4.5" cy="6" r="1" fill="currentColor"/><circle cx="4.5" cy="12" r="1" fill="currentColor"/><circle cx="4.5" cy="18" r="1" fill="currentColor"/></svg>
                    </a>
                    <a href="{{ $here(['layout' => 'board']) }}" class="ld-icon-btn plain-link {{ $layout === 'board' ? 'is-on' : '' }}" title="Board by status" aria-label="Board view" @if($layout === 'board') aria-current="page" @endif>
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4" width="17" height="7" rx="1.5"/><rect x="3.5" y="13" width="17" height="7" rx="1.5"/></svg>
                    </a>
                </div>

                @if($isOffice)
                <div class="flex items-center gap-3 flex-wrap" x-show="selected.length > 0" x-cloak>
                    <span class="font-semibold" x-text="selected.length + (selected.length === 1 ? ' lead selected' : ' leads selected')"></span>
                    <button type="button" class="text-sm underline" style="color: var(--ld-muted)" @click="selected = []">Clear</button>

                    <form method="POST" action="{{ route('leads.bulk') }}" class="relative" @click.outside="menu === 'status' && (menu = null)">
                        @csrf
                        <input type="hidden" name="action" value="status">
                        <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                        <button type="button" class="ld-outline" :aria-expanded="menu === 'status'" @click="open('status')">
                            Change status
                            <svg class="w-3 h-3 ml-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="ld-menu left-0" x-show="menu === 'status'" x-cloak>
                            @foreach($statusLabels as $key => $label)
                                <button type="submit" name="status" value="{{ $key }}" class="btn-submit">{{ $label }}</button>
                            @endforeach
                        </div>
                    </form>

                    @if($isAdmin)
                        <form method="POST" action="{{ route('leads.bulk') }}" x-bind:data-confirm="'Delete ' + selected.length + (selected.length === 1 ? ' lead' : ' leads') + '? This cannot be undone.'">
                            @csrf
                            <input type="hidden" name="action" value="delete">
                            <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                            <button type="submit" class="ld-outline btn-cancel" style="color: #b91c1c !important">Delete</button>
                        </form>
                    @endif
                </div>
                @endif

                <div class="flex items-center gap-2 ml-auto" x-show="selected.length === 0">
                    @if($isOffice)
                        <div class="relative" @click.outside="menu === 'create' && (menu = null)">
                            {{-- one shape: shared outline and corners, a thin divider before the arrow --}}
                            <div class="ld-split">
                                <a href="{{ route('leads.create') }}" class="plain-link">Create Lead</a>
                                <button type="button" aria-label="More ways to create a lead" :aria-expanded="menu === 'create'" @click="open('create')">
                                    <span class="ld-caret" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="ld-menu right-0 top-full" x-show="menu === 'create'" x-cloak>
                                <a href="{{ route('leads.create') }}" class="plain-link">Create lead</a>
                                <a href="{{ route('leads.create', ['for_quote' => 1]) }}" class="plain-link">Create lead and quotation</a>
                            </div>
                        </div>
                    @endif

                    <div class="relative" @click.outside="menu === 'more' && (menu = null)">
                        <button type="button" class="ld-outline" title="More actions" aria-label="More actions" :aria-expanded="menu === 'more'" @click="open('more')">
                            <svg class="w-4 h-4 icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                        </button>
                        <div class="ld-menu right-0" x-show="menu === 'more'" x-cloak>
                            @if($isOffice)
                                <a href="{{ route('leads.export', request()->except('page', 'layout', 'per_page')) }}" class="plain-link">Export these leads (CSV)</a>
                            @endif
                            <button type="button" @click="menu = null; window.print()">Print this page</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ld-body" x-ref="body">
            {{-- Filter panel --}}
            <div class="ld-card ld-filter" role="search" x-show="showFilter" x-cloak aria-label="Filter leads">
                <form method="GET" action="{{ route('leads.index') }}" class="flex flex-col h-full min-h-0">
                    @foreach(['view' => $view === 'all' ? null : $view, 'sort' => $sort === 'date_desc' ? null : $sort, 'layout' => $layout === 'board' ? 'board' : null, 'per_page' => $perPage === 20 ? null : $perPage, 'letter' => $letter] as $keep => $value)
                        @if($value)<input type="hidden" name="{{ $keep }}" value="{{ $value }}">@endif
                    @endforeach

                    <div class="ld-filter-scroll">
                        <h2>Filter Leads by</h2>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-2.5 top-2" style="color: var(--ld-muted)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                            <input type="search" x-model="filterQuery" placeholder="Search" aria-label="Search the filters" class="ld-input" style="padding-left: 32px !important">
                        </div>

                        <details open>
                            <summary><span class="ld-caret ld-caret-sm" aria-hidden="true"></span><span title="System Defined Filters">System Defined Filters</span></summary>
                            @foreach($quickFilters as $key => $label)
                                <label class="ld-check" x-show="shows(@js($label))">
                                    <input type="checkbox" name="only[]" value="{{ $key }}" @checked(in_array($key, $only, true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </details>

                        <details open>
                            <summary><span class="ld-caret ld-caret-sm" aria-hidden="true"></span><span>Filter By Fields</span></summary>

                            <div x-show="shows('name phone email search')">
                                <div class="ld-sub">Name, phone or email</div>
                                <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Name, phone or email">
                            </div>

                            <div x-show="shows('status new contacted quoted won lost')">
                                <div class="ld-sub">Lead Status</div>
                                @foreach($statusLabels as $key => $label)
                                    <label class="ld-check">
                                        <input type="checkbox" name="status[]" value="{{ $key }}" @checked(in_array($key, $pickedStatus, true))>
                                        <span>{{ $label }}</span>
                                        <span class="ld-count">{{ $statusCounts[$key] ?? 0 }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @if($sources->isNotEmpty())
                                <div x-show="shows(@js('lead source ' . $sources->implode(' ')))">
                                    <div class="ld-sub">Lead Source</div>
                                    @foreach($sources as $source)
                                        <label class="ld-check">
                                            <input type="checkbox" name="source[]" value="{{ $source }}" @checked(in_array($source, $pickedSource, true))>
                                            <span>{{ $source }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </details>
                    </div>

                    <div class="ld-filter-actions">
                        <button type="submit" id="ld-apply" class="ld-accent" style="border-radius: 4px; height: 30px; padding: 0 12px">Apply Filter</button>
                        @if($filterCount > 0)
                            <a href="{{ route('leads.index', array_filter(['view' => $view === 'all' ? null : $view, 'sort' => $sort === 'date_desc' ? null : $sort, 'layout' => $layout === 'board' ? 'board' : null])) }}" class="ld-outline plain-link" style="height: 30px">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <section class="ld-main" aria-label="Leads">
                @if($layout === 'board')
                    {{-- Board: every matching lead (up to 200), one lane per status --}}
                    <div class="ld-board">
                        @foreach($statusLabels as $key => $label)
                            @php $lane = $board->get($key, collect()); @endphp
                            <div class="ld-lane">
                                <h3><span>{{ $label }}</span><span style="color: var(--ld-muted); font-weight: 400">{{ $lane->count() }}</span></h3>
                                <div class="ld-lane-list">
                                    @forelse($lane as $lead)
                                        <a href="{{ route('leads.show', $lead) }}" class="ld-card-item plain-link">
                                            <div class="font-medium">{{ $lead->customer_name }}</div>
                                            @if($lead->company_name)<div class="text-sm" style="color: var(--ld-muted)">{{ $lead->company_name }}</div>@endif
                                            <div class="text-sm mt-1" style="color: var(--ld-muted)">{{ $lead->phone }}@if($lead->source) · {{ $lead->source }}@endif</div>
                                        </a>
                                    @empty
                                        <p class="text-sm px-1 py-2" style="color: var(--ld-muted)">No leads</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="ld-card flex flex-col flex-1 min-h-0">
                        <div class="ld-scroll">
                            <table class="ld-table">
                                <thead>
                                    <tr>
                                        @if($isOffice)
                                            <th class="ld-box"><input type="checkbox" aria-label="Select all leads on this page" :checked="allPicked" @change="toggleAll()"></th>
                                        @endif
                                        <th style="min-width: 210px">
                                            <div class="flex items-center gap-3">
                                                <span>Lead Name</span>
                                                <div class="relative" @click.outside="menu === 'letter' && (menu = null)">
                                                    <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold" :aria-expanded="menu === 'letter'" @click="open('letter')" aria-label="Show names starting with a letter">
                                                        {{ $letter ?? 'All' }}
                                                        <span class="ld-caret ld-caret-sm" aria-hidden="true"></span>
                                                    </button>
                                                    <div class="ld-menu ld-letters left-0" x-show="menu === 'letter'" x-cloak>
                                                        <a href="{{ $here(['letter' => null]) }}" class="plain-link {{ $letter ? '' : 'is-current' }}" style="grid-column: span 2">All</a>
                                                        @foreach(range('A', 'Z') as $l)
                                                            <a href="{{ $here(['letter' => $l]) }}" class="plain-link {{ $letter === $l ? 'is-current' : '' }}">{{ $l }}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th x-show="cols.company" style="min-width: 190px">Company</th>
                                        <th x-show="cols.email" style="min-width: 220px">Email</th>
                                        <th x-show="cols.phone" style="min-width: 150px">Phone</th>
                                        <th x-show="cols.source" style="min-width: 170px">Lead Source</th>
                                        <th x-show="cols.status" style="min-width: 120px">Lead Status</th>
                                        <th x-show="cols.created" style="min-width: 180px">Created Time</th>
                                        <th style="width: 72px; text-align: right">
                                            <div class="relative inline-block" @click.outside="menu === 'cols' && (menu = null)">
                                                <button type="button" class="ld-icon-btn" title="Choose columns" aria-label="Choose columns" :aria-expanded="menu === 'cols'" @click="open('cols')">
                                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/></svg>
                                                </button>
                                                <div class="ld-menu right-0 text-left" x-show="menu === 'cols'" x-cloak>
                                                    @foreach(['company' => 'Company', 'email' => 'Email', 'phone' => 'Phone', 'source' => 'Lead Source', 'status' => 'Lead Status', 'created' => 'Created Time'] as $col => $label)
                                                        <label><span>{{ $label }}</span><input type="checkbox" x-model="cols.{{ $col }}" style="accent-color: var(--ld-accent)"></label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        <tr :class="selected.includes({{ $lead->id }}) && 'is-picked'">
                                            @if($isOffice)
                                                <td class="ld-box"><input type="checkbox" value="{{ $lead->id }}" x-model.number="selected" aria-label="Select {{ $lead->customer_name }}"></td>
                                            @endif
                                            <td><a href="{{ route('leads.show', $lead) }}" class="ld-name plain-link">{{ $lead->customer_name }}</a></td>
                                            <td x-show="cols.company">{{ $lead->company_name ?: '' }}</td>
                                            <td x-show="cols.email"><span class="ld-wrap block">{{ $lead->email }}</span></td>
                                            <td x-show="cols.phone" class="whitespace-nowrap">{{ $lead->phone }}</td>
                                            <td x-show="cols.source">{{ $lead->source }}</td>
                                            <td x-show="cols.status">{{ $statusLabels[$lead->status] ?? ucfirst($lead->status) }}</td>
                                            <td x-show="cols.created" class="whitespace-nowrap">{{ $lead->created_at?->format('d/m/Y h:i A') }}</td>
                                            <td>
                                                <div class="ld-row-actions">
                                                    @if($isOffice)
                                                        <a href="{{ route('leads.edit', $lead) }}" class="plain-link" title="Edit" aria-label="Edit {{ $lead->customer_name }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                                                        </a>
                                                    @endif
                                                    @if($isAdmin)
                                                        <form method="POST" action="{{ route('leads.destroy', $lead) }}" data-confirm="Delete lead {{ $lead->customer_name }}?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $lead->customer_name }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" style="padding: 48px 16px; text-align: center; color: var(--ld-muted)">
                                                @if($filterCount > 0 || $letter || $view !== 'all')
                                                    No leads match these filters.
                                                    <a href="{{ route('leads.index') }}" class="ml-1 underline plain-link" style="color: var(--ld-accent, var(--crm-accent, #2563eb)); font-weight: 600;">Show all leads</a>
                                                @else
                                                    No leads yet.
                                                    @if($isOffice)<a href="{{ route('leads.create') }}" class="ml-1 underline plain-link" style="color: var(--ld-accent, var(--crm-accent, #2563eb)); font-weight: 600;">Create the first one</a>@endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="ld-foot">
                            <div>Total Records <strong>{{ $leads->total() }}</strong></div>
                            <div class="flex items-center gap-4">
                                <form method="GET" action="{{ route('leads.index') }}">
                                    @foreach(request()->except('per_page', 'page') as $k => $v)
                                        @foreach((array) $v as $item)
                                            <input type="hidden" name="{{ is_array($v) ? $k . '[]' : $k }}" value="{{ $item }}">
                                        @endforeach
                                    @endforeach
                                    <select name="per_page" onchange="this.form.submit()" aria-label="Records per page">
                                        @foreach([10, 20, 50, 100] as $n)
                                            <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }} Records Per Page</option>
                                        @endforeach
                                    </select>
                                </form>
                                <div class="flex items-center gap-1">
                                    @if($leads->onFirstPage())
                                        <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                                    @else
                                        <a href="{{ $leads->previousPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Previous page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                                    @endif
                                    <span class="px-1">
                                        @if($leads->total() > 0)
                                            <strong>{{ $leads->firstItem() }}</strong> to <strong>{{ $leads->lastItem() }}</strong>
                                        @else
                                            0
                                        @endif
                                    </span>
                                    @if($leads->hasMorePages())
                                        <a href="{{ $leads->nextPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Next page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                                    @else
                                        <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
