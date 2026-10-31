@props(['mark' => false, 'dark' => null])
{{--
    Precision IT Systems Official Standard Brand Logo
    - Crisp standard brand logo (white text on dark backgrounds, standard navy on light)
    - No dynamic CSS mask tinting, keeping authentic corporate branding intact.
--}}
@php
    $logoLight = $mark ? 'logo-mark.png' : 'logo.png';
    $logoDark = $mark ? 'logo-mark-dark.png' : 'logo-dark.png';
    $url = fn (string $file) => asset($file) . '?v=' . (file_exists(public_path($file)) ? filemtime(public_path($file)) : '1');
@endphp
<span role="img" aria-label="Precision IT Systems" {{ $attributes->merge(['class' => 'crm-brand-logo relative inline-flex items-center select-none ' . ($mark ? 'crm-brand-logo-mark' : '')]) }}>
    @if($dark === true)
        <img src="{{ $url($logoDark) }}" alt="Precision IT Systems" width="{{ $mark ? 512 : 962 }}" height="{{ $mark ? 512 : 259 }}" class="h-full w-auto max-w-full object-contain shrink-0">
    @elseif($dark === false)
        <img src="{{ $url($logoLight) }}" alt="Precision IT Systems" width="{{ $mark ? 512 : 962 }}" height="{{ $mark ? 512 : 259 }}" class="h-full w-auto max-w-full object-contain shrink-0">
    @else
        <img src="{{ $url($logoLight) }}" alt="Precision IT Systems" width="{{ $mark ? 512 : 962 }}" height="{{ $mark ? 512 : 259 }}" class="h-full w-auto max-w-full object-contain dark:hidden shrink-0">
        <img src="{{ $url($logoDark) }}" alt="Precision IT Systems" width="{{ $mark ? 512 : 962 }}" height="{{ $mark ? 512 : 259 }}" class="h-full w-auto max-w-full object-contain hidden dark:block shrink-0">
    @endif
</span>
