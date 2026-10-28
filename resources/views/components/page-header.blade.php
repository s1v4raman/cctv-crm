{{--
    Standard page header for the layout's header slot: title (optional subtitle and
    back link) with the page actions on the right. Wraps on phones.

    <x-slot name="header">
        <x-page-header title="Leads">
            <x-slot name="actions"><a href="…" class="btn-primary">New Lead</a></x-slot>
        </x-page-header>
    </x-slot>
--}}
@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'backLabel' => 'Back',
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-3']) }}>
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="back-link">&larr; {{ $backLabel }}</a>
        @endif
        <h2 class="text-xl font-bold text-[var(--text-main)]">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-0.5 text-sm text-[var(--text-muted)]">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
