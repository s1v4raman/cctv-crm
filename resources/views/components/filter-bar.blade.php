{{--
    List filter bar: a GET form with a search box, any extra filters in the slot
    (usually <x-filter-select>), and Filter / Reset buttons. Selects apply straight
    away (data-auto-submit, see resources/js/filters.js); Reset shows once a filter is set.

    <x-filter-bar :action="route('leads.index')" placeholder="Search customer, phone, address…">
        <x-filter-select name="status" label="Status" placeholder="All Statuses" :options="[...]" />
    </x-filter-bar>
--}}
@props([
    'action',
    'search' => 'search',
    'placeholder' => 'Search…',
    'searchLabel' => null,
    'filters' => ['search', 'status'],
])

<form method="GET" action="{{ $action }}" role="search" data-auto-submit
      {{ $attributes->merge(['class' => 'p-2.5 border-b border-[var(--card-border)] bg-[var(--card-bg)] flex items-center gap-2.5 flex-wrap text-xs']) }}>
    @if ($search)
        <div class="relative w-72 min-w-[220px] max-w-full flex items-center shrink-0">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="{{ $search }}" value="{{ request($search) }}"
                   placeholder="{{ $placeholder }}"
                   aria-label="{{ $searchLabel ?? rtrim($placeholder, '….') }}"
                   class="crm-input crm-filter-search w-full text-xs border border-[var(--input-border)] bg-[var(--card-bg)] text-[var(--text-main)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500/30">
        </div>
    @endif

    {{ $slot }}

    <button type="submit" class="crm-btn-primary px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition shrink-0 inline-flex items-center justify-center cursor-pointer">
        Filter
    </button>
    @if (request()->anyFilled($filters))
        <a href="{{ $action }}" class="crm-btn-secondary px-3.5 text-xs font-medium border border-[var(--button-border)] bg-[var(--card-bg)] text-[var(--text-main)] hover:bg-[var(--page-bg)] transition inline-flex items-center shrink-0">
            Reset
        </a>
    @endif
</form>
