{{--
    Status pill. The tone comes from the status-{name} classes in app.css
    (won/paid → green, pending/quoted → amber, new/scheduled → blue, lost/overdue → red, …).

    <x-status-badge :status="$lead->status" />
    <x-status-badge status="in_progress" label="Working on it" />
--}}
@props([
    'status',
    'label' => null,
])

@php
    $slug = \Illuminate\Support\Str::of((string) $status)->lower()->trim()->replace(' ', '_')->value();
@endphp

<span {{ $attributes->merge(['class' => 'status-badge status-' . $slug]) }}>{{ $label ?? \Illuminate\Support\Str::headline($slug ?: '—') }}</span>
