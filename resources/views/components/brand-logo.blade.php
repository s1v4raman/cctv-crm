@props(['mark' => false])
{{--
    Precision IT Systems Adaptive Brand Logo
    - "PRECISION" wordmark: dynamically recolored via CSS var(--crm-accent)
    - "IT SYSTEMS": remains original navy in light mode and white in dark mode
    - Red chevrons >> & crosshair: remain original red
--}}
@php
    $prec = $mark ? 'logo-mark-precision.png' : 'logo-precision.png';
    $staticLight = $mark ? 'logo-mark-static.png' : 'logo-static.png';
    $staticDark = $mark ? 'logo-mark-static-dark.png' : 'logo-static-dark.png';
    $url = fn (string $file) => asset($file) . '?v=' . (file_exists(public_path($file)) ? filemtime(public_path($file)) : '1');
@endphp
<span role="img" aria-label="Precision IT Systems" {{ $attributes->merge(['class' => 'crm-brand-logo relative inline-flex items-center select-none ' . ($mark ? 'crm-brand-logo-mark' : '')]) }}>
    {{-- Dynamic PRECISION wordmark layer masked with CSS variable --}}
    <span class="crm-logo-prec-mask absolute inset-0 pointer-events-none"
          style="-webkit-mask-image: url('{{ $url($prec) }}'); mask-image: url('{{ $url($prec) }}'); -webkit-mask-size: contain; mask-size: contain; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center; background-color: var(--crm-accent, #2563eb);"></span>

    {{-- Static elements: Red chevrons >>, crosshair, and IT SYSTEMS text --}}
    <img src="{{ $url($staticLight) }}" alt="" class="h-full w-auto max-w-full object-contain pointer-events-none dark:hidden shrink-0">
    <img src="{{ $url($staticDark) }}" alt="" class="h-full w-auto max-w-full object-contain pointer-events-none hidden dark:block shrink-0">
</span>
