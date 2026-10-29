@props(['class' => 'w-5 h-5 shrink-0'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }} aria-hidden="true">
    {{-- Top Weather / Sun Visor Shield --}}
    <path d="M5 6.5h15.5c.83 0 1.5.67 1.5 1.5v.5"/>
    {{-- CCTV Bullet Camera Body --}}
    <path d="M6.5 7.5h12c1.38 0 2.5 1.12 2.5 2.5v4c0 1.38-1.12 2.5-2.5 2.5h-12c-1.38 0-2.5-1.12-2.5-2.5v-4c0-1.38 1.12-2.5 2.5-2.5z"/>
    {{-- Front Bezel Glass Divider --}}
    <line x1="17" y1="7.5" x2="17" y2="16.5"/>
    {{-- Camera Optical Surveillance Lens --}}
    <circle cx="11.5" cy="12" r="2.5"/>
    {{-- Mount Arm to Wall Base --}}
    <path d="M9 16.5v2.5a2 2 0 0 1-2 2H2.5"/>
    {{-- Wall Mounting Plate --}}
    <line x1="2.5" y1="18.5" x2="2.5" y2="23.5"/>
</svg>
