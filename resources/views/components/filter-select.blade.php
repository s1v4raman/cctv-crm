{{--
    A select for <x-filter-bar>: first option clears the filter, the current request
    value stays selected.

    <x-filter-select name="status" label="Status" placeholder="All Statuses"
                     :options="['new' => 'New', 'won' => 'Won']" />
--}}
@props([
    'name',
    'label',
    'options' => [],
    'placeholder' => 'All',
])

<select name="{{ $name }}" aria-label="{{ $label }}"
        {{ $attributes->merge(['class' => 'crm-select min-w-[140px] text-xs border border-[var(--input-border)] bg-[var(--card-bg)] text-[var(--text-main)] focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500/30 shrink-0']) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $value => $text)
        <option value="{{ $value }}" @selected((string) request($name) === (string) $value)>{{ $text }}</option>
    @endforeach
</select>
