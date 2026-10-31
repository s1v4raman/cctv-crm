{{-- The tick box at the start of a list row, when the list has mass actions for this user (see list-mass-actions) --}}
@aware(['key'])
@props(['id', 'label' => null])
@if((\App\Support\ListActions::forUser($key, auth()->user())['pick'] ?? false))
    <td class="ld-box"><input type="checkbox" value="{{ $id }}" x-model.number="selected" aria-label="Select {{ $label ?? 'record ' . $id }}"></td>
@endif
