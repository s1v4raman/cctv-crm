{{--
    List table: scrolls sideways on wide content and becomes labelled cards on phones
    (crm-responsive, see resources/js/responsive-tables.js). Columns are strings or
    ['label' => …, 'class' => …, 'style' => …]; rows go in the slot.

    <x-data-table class="leads-table" :columns="['Customer', 'Phone', ['label' => 'Actions', 'class' => 'text-right']]">
        @forelse ($leads as $lead) <tr>…</tr> @empty <x-table-empty :colspan="3">…</x-table-empty> @endforelse
    </x-data-table>
--}}
@props([
    'columns' => [],
])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'crm-responsive w-full']) }}>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    @php $column = is_array($column) ? $column : ['label' => $column]; @endphp
                    <th scope="col"
                        @if (! empty($column['class'])) class="{{ $column['class'] }}" @endif
                        @if (! empty($column['style'])) style="{{ $column['style'] }}" @endif>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
