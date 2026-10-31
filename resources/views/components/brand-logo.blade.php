@props(['mark' => false, 'dark' => null, 'color' => null])
{{--
    Precision IT Systems brand logo, in two layers:
    - the "PRECISION" word is a mask filled with the theme accent, so it follows Profile → Theme
      (pass color="#2563eb" to fix it, as the storefront does)
    - the red chevrons, the crosshair and "IT SYSTEMS" stay as drawn: navy by day, white at night
--}}
@php
    $prec = $mark ? 'logo-mark-precision.png' : 'logo-precision.png';
    $staticLight = $mark ? 'logo-mark-static.png' : 'logo-static.png';
    $staticDark = $mark ? 'logo-mark-static-dark.png' : 'logo-static-dark.png';
    $url = fn (string $file) => asset($file) . '?v=' . (file_exists(public_path($file)) ? filemtime(public_path($file)) : '1');
    // A fixed colour must beat the theme's !important accent rule, so it goes inline with !important
    $fill = $color ? e($color) . ' !important' : 'var(--crm-accent, #2563eb)';
    $size = $mark ? 'width="512" height="512"' : 'width="962" height="259"';
@endphp
<span role="img" aria-label="Precision IT Systems" {{ $attributes->merge(['class' => 'crm-brand-logo relative inline-flex items-center select-none ' . ($mark ? 'crm-brand-logo-mark' : '')]) }}>
    <span class="crm-logo-prec-mask absolute inset-0 pointer-events-none" aria-hidden="true"
          style="-webkit-mask: url('{{ $url($prec) }}') center / contain no-repeat; mask: url('{{ $url($prec) }}') center / contain no-repeat; background-color: {!! $fill !!};"></span>
    @if($dark === true)
        <img src="{{ $url($staticDark) }}" alt="" {!! $size !!} class="relative h-full w-auto max-w-full object-contain pointer-events-none shrink-0">
    @elseif($dark === false)
        <img src="{{ $url($staticLight) }}" alt="" {!! $size !!} class="relative h-full w-auto max-w-full object-contain pointer-events-none shrink-0">
    @else
        <img src="{{ $url($staticLight) }}" alt="" {!! $size !!} class="relative h-full w-auto max-w-full object-contain pointer-events-none dark:hidden shrink-0">
        <img src="{{ $url($staticDark) }}" alt="" {!! $size !!} class="relative h-full w-auto max-w-full object-contain pointer-events-none hidden dark:block shrink-0">
    @endif
</span>
