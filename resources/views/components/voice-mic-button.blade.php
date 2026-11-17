@props([
    'target',
    'label' => __('Speak'),
    'class' => '',
    'mode' => 'append'
])

<button type="button" 
        onclick="window.techVoice && window.techVoice.dictate('{{ $target }}', this, { mode: '{{ $mode }}' })" 
        {{ $attributes->merge(['class' => 'tech-voice-btn inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-blue-50 dark:bg-slate-800 dark:hover:bg-blue-950/60 text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 transition cursor-pointer select-none ' . $class]) }}
        title="{{ __('Click to speak via Microphone') }}"
        data-target="{{ $target }}">
    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
    </svg>
    <span class="tech-voice-btn-text">{{ $label }}</span>
</button>
