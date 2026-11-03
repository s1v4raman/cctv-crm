{{-- The mic and on-screen Tamil keyboard shared by the worker sign-in and the worker / technician text boxes
     (<x-tamil-input>). The mic listens in the page's language: Tamil (ta-IN) or English (en-IN), so what is
     spoken is written in the language the user picked. Whatever is typed, tapped or spoken is saved as entered. --}}
@once
<style>
    .name-tool-btn {
        width: 2rem; height: 2rem; border-radius: 0.5rem;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #cbd5e1; background: #ffffff; color: #334155; cursor: pointer;
    }
    .name-tool-btn:hover { border-color: #2563eb; color: #2563eb; }
    .name-tool-btn.is-on { background: #2563eb; border-color: #2563eb; color: #ffffff; }
    .name-tool-btn.is-listening { background: #e11d48; border-color: #e11d48; color: #ffffff; animation: micPulse 1.2s ease-in-out infinite; }
    @keyframes micPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }

    .tamil-input-tools { display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem; margin-top: 0.375rem; }
    .tamil-input-message { margin-right: auto; font-size: 11px; line-height: 1.3; color: #64748b; }

    .tamil-kbd { border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0.75rem; padding: 0.5rem; }
    .tamil-kbd-label { font-size: 11px; font-weight: 600; color: #64748b; margin: 0.25rem 0.125rem; }
    .tamil-kbd-row { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.3rem; }
    @media (min-width: 400px) { .tamil-kbd-row { grid-template-columns: repeat(9, minmax(0, 1fr)); } }
    .tamil-key {
        min-height: 2.5rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: #ffffff;
        color: #0f172a; font-size: 1.05rem; font-weight: 600; cursor: pointer; touch-action: manipulation;
        font-family: "Noto Sans Tamil", var(--font-sans);
    }
    .tamil-key:active { background: #dbeafe; border-color: #2563eb; }
    .tamil-key:disabled { opacity: 0.35; cursor: default; }
    .tamil-key-action { font-size: 0.75rem; background: #e2e8f0; }
    .tamil-kbd-actions { display: grid; grid-template-columns: 1fr 2fr 1fr 1fr; gap: 0.3rem; margin-top: 0.4rem; }

    html.dark .name-tool-btn { background: #1e293b; border-color: #334155; color: #e2e8f0; }
    html.dark .name-tool-btn:hover { border-color: #60a5fa; color: #93c5fd; }
    html.dark .name-tool-btn.is-on { background: #2563eb; border-color: #3b82f6; color: #ffffff; }
    html.dark .name-tool-btn.is-listening { background: #e11d48; border-color: #fb7185; color: #ffffff; }
    html.dark .tamil-input-message { color: #94a3b8; }
    html.dark .tamil-kbd { background: #0b1329; border-color: #1e293b; }
    html.dark .tamil-kbd-label { color: #94a3b8; }
    html.dark .tamil-key { background: #1e293b; border-color: #334155; color: #f8fafc; }
    html.dark .tamil-key:active { background: #1e3a8a; border-color: #3b82f6; }
    html.dark .tamil-key-action { background: #273449; }
</style>
<script>
    /**
     * Alpine data for a text box with the mic (Web Speech API) and the on-screen Tamil keyboard. Works on the
     * first input or textarea inside the element, and fires "input" after each change so x-model stays in step.
     * options.replace: what is spoken replaces the box (a name); otherwise it goes in where the cursor is (notes).
     * options.text: messages to use instead of the general ones below.
     */
    function tamilInput(options = {}) {
        const speechLang = {{ Js::from(app()->getLocale() === 'ta' ? 'ta-IN' : 'en-IN') }};
        const text = Object.assign({{ Js::from([
            'listening' => __('Listening… speak now'),
            'heard' => __('Check what was written, then save.'),
            'noSpeech' => __('We did not hear anything. Tap the mic and try again.'),
            'micBlocked' => __('The microphone is blocked. Allow it in your browser settings, or type instead.'),
            'failed' => __('Voice input is not working here. Please type instead.'),
        ]) }}, options.text || {});
        // Kept outside Alpine's reactive data: the browser's own objects must not be wrapped in proxies
        let root = null;
        let recognition = null;

        return {
            keyboardOpen: false,
            caret: null,
            typingMode: null,
            speechSupported: !!(window.SpeechRecognition || window.webkitSpeechRecognition),
            listening: false,
            voiceMessage: '',
            vowels: ['அ', 'ஆ', 'இ', 'ஈ', 'உ', 'ஊ', 'எ', 'ஏ', 'ஐ', 'ஒ', 'ஓ', 'ஔ', 'ஃ'],
            consonants: ['க', 'ங', 'ச', 'ஞ', 'ட', 'ண', 'த', 'ந', 'ப', 'ம', 'ய', 'ர', 'ல', 'வ', 'ழ', 'ள', 'ற', 'ன', 'ஜ', 'ஷ', 'ஸ', 'ஹ', 'க்ஷ', 'ஸ்ரீ'],
            signs: ['ா', 'ி', 'ீ', 'ு', 'ூ', 'ெ', 'ே', 'ை', 'ொ', 'ோ', 'ௌ', '்'],

            init() {
                root = this.$el;
                const field = this.field();
                if (!field) return;
                this.typingMode = field.getAttribute('inputmode');
                ['click', 'keyup', 'input', 'select'].forEach((name) => field.addEventListener(name, () => this.rememberCaret()));
            },

            field() {
                return root ? root.querySelector('input:not([type=hidden]), textarea') : null;
            },

            value() {
                return this.field()?.value ?? '';
            },

            setValue(value) {
                const field = this.field();
                field.value = value;
                field.dispatchEvent(new Event('input', { bubbles: true }));
            },

            limit() {
                const max = this.field()?.maxLength ?? -1;
                return max > 0 ? max : Infinity;
            },

            rememberCaret() {
                const field = this.field();
                if (!field) return;
                const length = field.value.length;
                this.caret = [field.selectionStart ?? length, field.selectionEnd ?? length];
            },

            caretRange() {
                const length = this.value().length;
                const [start, end] = this.caret ?? [length, length];
                return [Math.min(start, length), Math.min(end, length)];
            },

            lastChar() {
                const [start] = this.caretRange();
                return this.value().slice(0, start).slice(-1);
            },

            lastIsConsonant() {
                const code = this.lastChar().charCodeAt(0);
                return code >= 0x0B95 && code <= 0x0BB9;
            },

            insert(letters) {
                const value = this.value();
                const [start, end] = this.caretRange();
                if (value.length - (end - start) + letters.length > this.limit()) return;
                this.setValue(value.slice(0, start) + letters + value.slice(end));
                this.placeCaret(start + letters.length);
            },

            backspace() {
                const value = this.value();
                const [start, end] = this.caretRange();
                const from = start === end ? Math.max(0, start - 1) : start;
                this.setValue(value.slice(0, from) + value.slice(end));
                this.placeCaret(from);
            },

            clearField() {
                this.setValue('');
                this.placeCaret(0);
            },

            placeCaret(position) {
                this.caret = [position, position];
                this.$nextTick(() => {
                    const field = this.field();
                    field.focus({ preventScroll: true });
                    field.setSelectionRange(position, position);
                });
            },

            toggleKeyboard() {
                this.keyboardOpen = !this.keyboardOpen;
                const field = this.field();
                if (this.keyboardOpen) {
                    // So tapping the box does not pop the phone's own keyboard up over the Tamil one
                    field.setAttribute('inputmode', 'none');
                    this.caret = this.caret ?? this.caretRange();
                } else if (this.typingMode) {
                    field.setAttribute('inputmode', this.typingMode);
                } else {
                    field.removeAttribute('inputmode');
                }
            },

            toggleVoice() {
                if (this.listening && recognition) {
                    recognition.stop();
                    return;
                }

                const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!Recognition) return;

                const [start, end] = this.caretRange();
                recognition = new Recognition();
                recognition.lang = speechLang;
                recognition.interimResults = !!options.replace;
                recognition.continuous = false;
                recognition.maxAlternatives = 1;

                recognition.onstart = () => {
                    this.listening = true;
                    this.voiceMessage = text.listening;
                };
                recognition.onresult = (event) => {
                    let spoken = '';
                    for (let i = 0; i < event.results.length; i++) {
                        spoken += event.results[i][0].transcript;
                    }
                    spoken = spoken.trim();
                    const isFinal = event.results[event.results.length - 1].isFinal;

                    if (options.replace) {
                        this.setValue(spoken.slice(0, this.limit()));
                        this.caret = null;
                    } else if (isFinal && spoken) {
                        // Notes: the words go in where the cursor was, after a space
                        const before = this.value().slice(0, start);
                        this.caret = [start, end];
                        this.insert((before && !/\s$/.test(before) ? ' ' : '') + spoken);
                    }
                    if (isFinal) {
                        this.voiceMessage = text.heard;
                    }
                };
                recognition.onerror = (event) => {
                    this.voiceMessage = (event.error === 'not-allowed' || event.error === 'service-not-allowed')
                        ? text.micBlocked
                        : (event.error === 'no-speech' ? text.noSpeech : text.failed);
                };
                recognition.onend = () => {
                    this.listening = false;
                    recognition = null;
                };

                try {
                    recognition.start();
                } catch (e) {
                    this.listening = false;
                    this.voiceMessage = text.failed;
                }
            },
        };
    }
</script>
@endonce
