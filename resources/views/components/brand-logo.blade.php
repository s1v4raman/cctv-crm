@props(['mark' => false])
{{--
    The Precision IT Systems logo in the theme colour (Profile -> Theme): the wordmark is a shape
    filled with --crm-logo-color, the red arrows and crosshair stay red. Styles: .crm-logo in app.css.
    Made from logo.png / logo-mark.png; PDFs and the public pages keep using those images.
--}}
@php
    $word = $mark ? 'logo-mark-word.png' : 'logo-word.png';
    $red = $mark ? 'logo-mark-red.png' : 'logo-red.png';
    $url = fn (string $file) => asset($file) . '?v=' . filemtime(public_path($file));
@endphp
<span role="img" aria-label="Precision IT Systems" {{ $attributes->merge(['class' => 'crm-logo' . ($mark ? ' crm-logo-mark' : '')]) }}>
    <span class="crm-logo-word" style="-webkit-mask-image: url('{{ $url($word) }}'); mask-image: url('{{ $url($word) }}');"></span>
    <img src="{{ $url($red) }}" alt="" class="crm-logo-red">
</span>
