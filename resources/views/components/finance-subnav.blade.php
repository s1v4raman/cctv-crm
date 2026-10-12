@props(['active' => 'hub'])

@php
    $isHub = $active === 'hub' || request()->routeIs('finance.hub') || request()->is('finance-accounting*');
@endphp

<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('finance.hub') }}"
       class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition"
       style="border-radius: 4px;">
        <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Finance &amp; Accounting</span>
    </a>
</div>
