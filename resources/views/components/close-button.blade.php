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
    'inline-flex items-center justify-center rounded-lg transition shadow-xs cursor-pointer',
    $size === 'sm' ? 'w-7 h-7 min-w-[28px] min-h-[28px]' : 'w-8 h-8 sm:w-9 sm:h-9 min-w-[32px] min-h-[32px]',
    $tone === 'danger'
        ? 'bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400'
        : ($tone === 'dark'
            ? 'bg-slate-800/90 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white'
            : ($tone === 'inherit'
                ? 'bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/20 text-current'
                : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white shadow-xs'
            )
        )
]) }}>
    <svg class="{{ $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M18 6L6 18M6 6l12 12"/>
    </svg>
</button>
