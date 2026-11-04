{{--
    The plus in front of an "add" button's label. Use it instead of typing "+" into the label,
    so a button never shows two plus signs and every add button looks the same.
--}}
@props(['class' => 'w-3.5 h-3.5 shrink-0'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2.25', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }} aria-hidden="true">
    <path d="M12 5v14M5 12h14"/>
</svg>
