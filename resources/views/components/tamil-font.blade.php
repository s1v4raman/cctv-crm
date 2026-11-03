@props([
    // The page's own font stack with Noto Sans Tamil added: Latin text keeps the first font,
    // Tamil letters (the Tamil interface, names typed in Tamil) use Noto Sans Tamil.
    'stack' => '"Roboto", "Noto Sans Tamil", ui-sans-serif, system-ui, -apple-system, "Segoe UI", "Helvetica Neue", Arial, sans-serif',
])
{{-- Cheap Android phones may lack a good Tamil font. Google Fonts serves it split by script,
     so the Tamil file is only downloaded when Tamil text is on the page. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;500;600;700&display=swap">
<style>
    :root { --font-sans: {!! $stack !!}; }
    body, .font-sans, .font-heading { font-family: var(--font-sans); }
</style>
