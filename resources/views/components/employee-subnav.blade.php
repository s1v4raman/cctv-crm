@props(['active' => 'hub'])

@php
    $isHub = $active === 'hub' || request()->routeIs('employee.hub') || request()->is('employee-workforce*');
@endphp

<div class="mb-6 flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-1.5 rounded-2xl shadow-xs w-fit">
    <a href="{{ route('employee.hub') }}"
       class="crm-hub-subnav-pill inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition-all duration-200 shadow-xs hover:opacity-95"
       style="background: linear-gradient(135deg, var(--crm-accent, #be123c), var(--crm-accent-hover, #9f1239)); border: 1px solid var(--crm-accent, #be123c); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(190,18,60,0.35));">
        <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span class="text-white font-bold text-[13px] leading-none">Employee &amp; Workspace</span>
    </a>
</div>
