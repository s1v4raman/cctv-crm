{{--
    The × that closes a dialog, drawer or message, or removes an item. One size and icon everywhere,
    so it never shrinks to the 13px text the site-wide button rule sets.
    Give it the click handler as usual (@click, x-on:click or onclick); the label defaults to "Close".
      size="sm"       28px, for flash messages, search boxes and rows (36px otherwise, for dialog headers)
      tone="inherit"  takes the text colour around it, for coloured flash messages
      tone="dark"     on a dark surface: photo viewers, cameras, dark dialogs
      tone="danger"   red on hover, for removing an item
--}}
@props(['label' => 'Close', 'size' => null, 'tone' => null])

<button {{ $attributes->merge(['type' => 'button', 'aria-label' => $label, 'title' => $label])->class([
    'crm-close btn-icon',
    'crm-close--sm' => $size === 'sm',
    'crm-close--' . $tone => $tone,
]) }}>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
</button>
