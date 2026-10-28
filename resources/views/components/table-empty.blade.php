{{-- Full-width "no rows" row for <x-data-table>; put <x-empty-message> (or any text) in the slot. --}}
@props([
    'colspan' => 1,
])

<tr>
    <td colspan="{{ $colspan }}" {{ $attributes->merge(['class' => 'text-center py-6 text-[var(--text-muted)]']) }}>
        {{ $slot }}
    </td>
</tr>
