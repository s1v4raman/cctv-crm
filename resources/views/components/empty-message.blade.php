{{-- Empty-state text for list tables: tells "nothing yet" apart from "nothing matches these filters". --}}
@props([
    'empty',
    'filtered' => 'Nothing matches these filters.',
    'filters' => ['search', 'status'],
])

@if (request()->anyFilled($filters))
    <span>{{ $filtered }}</span>
    <a href="{{ url()->current() }}" class="ml-1 font-semibold text-[var(--crm-accent)] hover:underline">Clear filters</a>
@else
    <span>{{ $empty }}</span>
@endif
