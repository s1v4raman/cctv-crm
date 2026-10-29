@props(['class' => 'w-4 h-4 shrink-0'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }} aria-hidden="true">
    {{-- Outer rounded envelope border --}}
    <rect x="2" y="4" width="20" height="16" rx="3.5" ry="3.5"/>
    {{-- Top flap with rounded apex pointing downward --}}
    <path d="M3 6.5l7.2 5.4a3 3 0 0 0 3.6 0L21 6.5"/>
    {{-- Left diagonal crease fold --}}
    <path d="M3.5 17.5l6.2-5.5"/>
    {{-- Right diagonal crease fold --}}
    <path d="M20.5 17.5l-6.2-5.5"/>
</svg>
