{{--
    A record list in the shared list design (see list-page-assets): views chip and menu,
    Filter / Sort tools, optional Create split button, More menu, filter panel, table with a
    column chooser, and the Total Records / per page / page arrows footer.

    The page passes its own filter fields (slot "filters"), header cells (slot "head") and
    rows (default slot). Columns listed in :columns can be hidden from the chooser; give
    their <th>/<td> x-show="cols.key".
--}}
@props([
    'key',
    'paginator',
    'views' => [],          // [['label' => 'All', 'url' => ..., 'active' => bool], ...]
    'sorts' => [],          // ['sort_key' => 'Label'], first one is the default
    'createUrl' => null,
    'createLabel' => null,
    'createMenu' => [],     // ['Label' => url]
    'exportUrl' => null,
    'filterTitle' => 'Filter by',
    'filterAction',
    'keep' => [],           // hidden fields kept when filters are applied
    'filterCount' => 0,
    'clearUrl' => null,
    'columns' => [],        // ['key' => 'Label'] for the column chooser
    'emptyText' => 'Nothing here yet.',
    'filteredText' => 'Nothing matches these filters.',
])

@php
    $currentView = collect($views)->firstWhere('active', true) ?? ($views[0] ?? ['label' => 'All']);
    $sortKeys = array_keys($sorts);
    $currentSort = in_array(request('sort'), $sortKeys, true) ? request('sort') : ($sortKeys[0] ?? null);
    $perPage = in_array((int) request('per_page'), [10, 20, 50, 100], true) ? (int) request('per_page') : $paginator->perPage();
    $here = fn (array $change) => request()->fullUrlWithQuery(array_merge(['page' => null], $change));
    $hasFilters = trim((string) ($filters ?? '')) !== '';
@endphp

<x-list-page-assets />

<div class="ld-root" x-data="listPage([], @js($key), @js(array_fill_keys(array_keys($columns), true)), @js($filterCount > 0))" @keydown.escape.window="menu = null">

    @if (session('status'))
        <div class="px-4 py-2 text-sm bg-emerald-50 text-emerald-800 border-b border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="px-4 py-2 text-sm bg-rose-50 text-rose-800 border-b border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900">{{ session('error') }}</div>
    @endif

    @isset($above)
        {{-- e.g. a module section menu (Finance) above the views --}}
        <div class="ld-above">{{ $above }}</div>
    @endisset

    <div class="ld-band">
        <div class="ld-views">
            <span class="ld-chip">{{ $currentView['label'] }}</span>
            @if(count($views) > 1)
                <div class="relative" @click.outside="menu === 'views' && (menu = null)">
                    <button type="button" class="ld-icon-btn" title="Other views" aria-label="Other views" :aria-expanded="menu === 'views'" @click="open('views')">
                        <svg class="icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                    </button>
                    <div class="ld-menu left-0" x-show="menu === 'views'" x-cloak>
                        @foreach($views as $v)
                            <a href="{{ $v['url'] }}" class="plain-link {{ $v['active'] ? 'is-current' : '' }}">
                                <span>{{ $v['label'] }}</span>
                                @if($v['active'])<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="ld-toolbar">
            <div class="flex items-center gap-1">
                @if($hasFilters)
                    <button type="button" class="ld-tool" :class="showFilter && 'is-on'" @click="showFilter = !showFilter; $nextTick(() => fit())" :aria-pressed="showFilter">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18l-7 8.5V19l-4 2v-7.5L3 5z"/></svg>
                        <span>Filter</span>
                        @if($filterCount > 0)<span class="text-xs font-semibold" style="color: var(--ld-accent)">{{ $filterCount }}</span>@endif
                    </button>
                @endif

                @if(count($sorts) > 0)
                    <div class="relative" @click.outside="menu === 'sort' && (menu = null)">
                        <button type="button" class="ld-tool" :aria-expanded="menu === 'sort'" @click="open('sort')">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4v16m0 0l-3-3m3 3l3-3M16 20V4m0 0l-3 3m3-3l3 3"/></svg>
                            <span>Sort</span>
                        </button>
                        <div class="ld-menu left-0" x-show="menu === 'sort'" x-cloak>
                            @foreach($sorts as $sk => $label)
                                <a href="{{ $here(['sort' => $sk === $sortKeys[0] ? null : $sk]) }}" class="plain-link {{ $currentSort === $sk ? 'is-current' : '' }}">
                                    <span>{{ $label }}</span>
                                    @if($currentSort === $sk)<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{ $tools ?? '' }}
            </div>

            <div class="flex items-center gap-2 ml-auto">
                @if($createUrl)
                    @if(count($createMenu) > 0)
                        <div class="relative" @click.outside="menu === 'create' && (menu = null)">
                            <div class="ld-split">
                                <a href="{{ $createUrl }}" class="plain-link">{{ $createLabel }}</a>
                                <button type="button" aria-label="More ways to {{ strtolower($createLabel) }}" :aria-expanded="menu === 'create'" @click="open('create')">
                                    <span class="ld-caret" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="ld-menu right-0 top-full" x-show="menu === 'create'" x-cloak>
                                @foreach($createMenu as $label => $url)
                                    <a href="{{ $url }}" class="plain-link">{{ $label }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="ld-split"><a href="{{ $createUrl }}" class="plain-link">{{ $createLabel }}</a></div>
                    @endif
                @endif

                {{ $actions ?? '' }}

                <div class="relative" @click.outside="menu === 'more' && (menu = null)">
                    <button type="button" class="ld-outline" title="More actions" aria-label="More actions" :aria-expanded="menu === 'more'" @click="open('more')">
                        <svg class="w-4 h-4 icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                    </button>
                    <div class="ld-menu right-0" x-show="menu === 'more'" x-cloak>
                        @if($exportUrl)<a href="{{ $exportUrl }}" class="plain-link">Export (CSV)</a>@endif
                        {{ $more ?? '' }}
                        <button type="button" @click="menu = null; window.print()">Print this page</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ld-body" x-ref="body">
        @if($hasFilters)
            <div class="ld-card ld-filter" role="search" x-show="showFilter" x-cloak aria-label="{{ $filterTitle }}">
                <form method="GET" action="{{ $filterAction }}" class="flex flex-col h-full min-h-0">
                    @foreach($keep as $name => $value)
                        @if(filled($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
                    @endforeach
                    <div class="ld-filter-scroll">
                        <h2>{{ $filterTitle }}</h2>
                        <details open>
                            <summary><span class="ld-caret ld-caret-sm" aria-hidden="true"></span><span>Filter By Fields</span></summary>
                            {{ $filters }}
                        </details>
                    </div>
                    <div class="ld-filter-actions">
                        <button type="submit" id="ld-apply" class="ld-accent" style="border-radius: 4px; height: 30px; padding: 0 12px">Apply Filter</button>
                        @if($filterCount > 0 && $clearUrl)
                            <a href="{{ $clearUrl }}" class="ld-outline plain-link" style="height: 30px">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        <section class="ld-main">
            <div class="ld-card flex flex-col flex-1 min-h-0">
                <div class="ld-scroll">
                    <table class="ld-table">
                        <thead>
                            <tr>
                                {{ $head }}
                                <th style="width: 72px; text-align: right">
                                    @if(count($columns) > 0)
                                        <div class="relative inline-block" @click.outside="menu === 'cols' && (menu = null)">
                                            <button type="button" class="ld-icon-btn" title="Choose columns" aria-label="Choose columns" :aria-expanded="menu === 'cols'" @click="open('cols')">
                                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/></svg>
                                            </button>
                                            <div class="ld-menu right-0 text-left" x-show="menu === 'cols'" x-cloak>
                                                @foreach($columns as $col => $label)
                                                    <label><span>{{ $label }}</span><input type="checkbox" x-model="cols.{{ $col }}" style="accent-color: var(--ld-accent)"></label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {{ $slot }}
                            @if($paginator->isEmpty())
                                <tr>
                                    <td colspan="20" style="padding: 48px 16px; text-align: center; color: var(--ld-muted)">
                                        @if($filterCount > 0 || (request()->except('page', 'per_page', 'sort') !== []))
                                            {{ $filteredText }}
                                            @if($clearUrl)<a href="{{ $clearUrl }}" class="ml-1 underline plain-link" style="color: var(--ld-accent, var(--crm-accent, #2563eb)); font-weight: 600;">Show all</a>@endif
                                        @else
                                            {{ $emptyText }}
                                            @if($createUrl)<a href="{{ $createUrl }}" class="ml-1 underline plain-link" style="color: var(--ld-accent, var(--crm-accent, #2563eb)); font-weight: 600;">{{ $createLabel }}</a>@endif
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="ld-foot">
                    <div>Total Records <strong>{{ $paginator->total() }}</strong></div>
                    <div class="flex items-center gap-4">
                        <form method="GET" action="{{ url()->current() }}">
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
                            @if($paginator->onFirstPage())
                                <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                            @else
                                <a href="{{ $paginator->previousPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Previous page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                            @endif
                            <span class="px-1">
                                @if($paginator->total() > 0)
                                    <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong>
                                @else
                                    0
                                @endif
                            </span>
                            @if($paginator->hasMorePages())
                                <a href="{{ $paginator->nextPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Next page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                            @else
                                <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
