{{-- On-screen Tamil keyboard: letters, then the vowel signs for the last consonant (க → கா கி கீ …).
     Driven by tamilInput() (x-tamil-input-script) on the surrounding x-data. --}}
<div x-show="keyboardOpen" x-cloak class="tamil-kbd mt-2" lang="ta" role="group" aria-label="{{ __('Tamil keyboard') }}">
    <div class="tamil-kbd-label">{{ __('Vowels') }}</div>
    <div class="tamil-kbd-row">
        <template x-for="letter in vowels" :key="letter">
            <button type="button" class="tamil-key" @click="insert(letter)" x-text="letter"></button>
        </template>
    </div>
    <div class="tamil-kbd-label">{{ __('Consonants') }}</div>
    <div class="tamil-kbd-row">
        <template x-for="letter in consonants" :key="letter">
            <button type="button" class="tamil-key" @click="insert(letter)" x-text="letter"></button>
        </template>
    </div>
    <div class="tamil-kbd-label">{{ __('Vowel signs (tap a consonant first)') }}</div>
    <div class="tamil-kbd-row">
        <template x-for="sign in signs" :key="sign">
            <button type="button" class="tamil-key" @click="insert(sign)" :disabled="!lastIsConsonant()"
                    x-text="(lastIsConsonant() ? lastChar() : '◌') + sign"></button>
        </template>
    </div>
    <div class="tamil-kbd-actions">
        <button type="button" class="tamil-key tamil-key-action" @click="clearField()">{{ __('Clear') }}</button>
        <button type="button" class="tamil-key tamil-key-action" @click="insert(' ')">{{ __('Space') }}</button>
        <button type="button" class="tamil-key tamil-key-action" @click="backspace()" aria-label="{{ __('Delete') }}">⌫</button>
        <button type="button" class="tamil-key tamil-key-action" @click="toggleKeyboard()">{{ __('Done') }}</button>
    </div>
</div>
