{{-- A worker's or technician's text box with the mic and the on-screen Tamil keyboard under it (x-tamil-input-script).
     What they type, tap or speak is saved exactly as entered. Office accounts just get the text box. --}}
@if (auth()->user()?->canChooseLanguage())
    <div x-data="tamilInput()" {{ $attributes }}>
        {{ $slot }}
        <div class="tamil-input-tools">
            <span x-show="voiceMessage" x-cloak x-text="voiceMessage" class="tamil-input-message" aria-live="polite"></span>
            {{-- Voice: only where the browser can turn speech into text (not Firefox, some iPhones) --}}
            <button type="button" x-show="speechSupported" x-cloak @click="toggleVoice()"
                    class="name-tool-btn" :class="listening ? 'is-listening' : ''"
                    :aria-pressed="listening.toString()"
                    :title="listening ? {{ Js::from(__('Stop listening')) }} : {{ Js::from(__('Speak')) }}"
                    :aria-label="listening ? {{ Js::from(__('Stop listening')) }} : {{ Js::from(__('Speak')) }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg>
            </button>
            <button type="button" @click="toggleKeyboard()"
                    class="name-tool-btn" :class="keyboardOpen ? 'is-on' : ''"
                    :aria-pressed="keyboardOpen.toString()"
                    title="{{ __('Tamil keyboard') }}" aria-label="{{ __('Tamil keyboard') }}">
                <span class="text-sm font-bold" lang="ta" aria-hidden="true">அ</span>
            </button>
        </div>
        <x-tamil-keyboard />
    </div>
@else
    {{ $slot }}
@endif
