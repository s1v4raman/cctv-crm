@props(['active' => 'hub'])

@php
    $isHub = $active === 'hub' || request()->routeIs('finance.hub') || request()->is('finance-accounting*');
@endphp

<div class="mb-6 flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-1.5 rounded-2xl shadow-xs w-fit">
    <a href="{{ route('finance.hub') }}"
       class="crm-hub-subnav-pill inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition-all duration-200 shadow-xs hover:opacity-95"
       style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
        <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-white font-bold text-[13px] leading-none">Finance &amp; Accounting</span>
    </a>
</div>
