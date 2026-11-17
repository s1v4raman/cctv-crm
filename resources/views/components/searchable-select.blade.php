@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'required' => false,
    'placeholder' => null,
    'searchPlaceholder' => null,
    'selected' => null,
    'options' => [],
    'actionLabel' => null,
    'actionUrl' => null,
    'actionClick' => null,
    'targetSelectId' => null,
])

@php
    $selectId = $id ?? ($name ? 'select_' . str_replace(['[', ']', '.'], '_', $name) : 'crm_searchable_' . uniqid());
    $resolvedPlaceholder = $placeholder ?? ($label ? "-- Select {$label} --" : '-- Select Option --');
    $resolvedSearchPlaceholder = $searchPlaceholder ?? ($label ? "Search {$label}..." : 'Search options...');
@endphp

<div class="crm-field-group mb-4">
    @if($label || $actionLabel)
        <div class="flex items-center justify-between mb-2">
            @if($label)
                <label for="{{ $selectId }}" class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">
                    {{ $label }}
                    @if($required)
                        <span class="required-marker text-rose-500">*</span>
                    @endif
                </label>
            @endif

            @if($actionLabel)
                @if($actionClick)
                    <button type="button" 
                            onclick="{{ $actionClick }}"
                            class="px-3 py-1 text-xs font-semibold rounded-lg bg-[var(--crm-accent,#2563eb)] hover:opacity-90 active:scale-95 text-white transition cursor-pointer shadow-xs"
                            style="color: #ffffff !important;">
                        <span style="color: #ffffff !important;">{{ $actionLabel }}</span>
                    </button>
                @elseif($actionUrl)
                    <a href="{{ $actionUrl }}" 
                       class="px-3 py-1 text-xs font-semibold rounded-lg bg-[var(--crm-accent,#2563eb)] hover:opacity-90 active:scale-95 text-white transition cursor-pointer shadow-xs inline-flex items-center"
                       style="color: #ffffff !important;">
                        <span style="color: #ffffff !important;">{{ $actionLabel }}</span>
                    </a>
                @endif
            @endif
        </div>
    @endif

    <div class="relative w-full">
        <select id="{{ $selectId }}"
                @if($name) name="{{ $name }}" @endif
                data-searchable="true"
                data-search-placeholder="{{ $resolvedSearchPlaceholder }}"
                class="crm-select crm-searchable-select w-full h-[44px] text-sm rounded-lg"
                @if($required) required @endif
                {{ $attributes }}>
            <option value="">{{ $resolvedPlaceholder }}</option>
            @if(is_array($options) || $options instanceof \Illuminate\Support\Collection)
                @foreach($options as $key => $opt)
                    @php
                        $val = is_object($opt) ? ($opt->id ?? $key) : (is_array($opt) ? ($opt['id'] ?? $key) : $key);
                        $txt = is_object($opt) ? ($opt->name ?? $opt->title ?? $opt->label ?? $val) : (is_array($opt) ? ($opt['name'] ?? $opt['title'] ?? $opt['label'] ?? $val) : $opt);
                        $isSelected = (string)$selected === (string)$val;
                    @endphp
                    <option value="{{ $val }}" @selected($isSelected)>{{ $txt }}</option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>
    </div>
</div>
