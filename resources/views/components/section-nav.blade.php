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
                   class="section-nav-link inline-block py-2.5 border-b-2 font-medium transition {{ $item['active'] ? 'border-blue-600 dark:border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
