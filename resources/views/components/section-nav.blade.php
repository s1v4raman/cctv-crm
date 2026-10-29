{{--
    Text menu for the pages of one module (Employees, Finance), shown under the page title.
    $items: [['label' => 'Attendance', 'url' => route(...), 'active' => bool], ...]
--}}
@props(['items' => [], 'label' => 'Section'])

<nav aria-label="{{ $label }}" class="mb-5 border-b border-[var(--card-border)] overflow-x-auto overflow-y-hidden">
    <ul class="flex gap-6 text-sm whitespace-nowrap">
        @foreach ($items as $item)
            <li>
                <a href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif
                   class="section-nav-link inline-block py-2.5 border-b-2 {{ $item['active'] ? 'border-[var(--crm-accent,#2563eb)] text-[var(--text-main)] font-medium' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-main)]' }}">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
