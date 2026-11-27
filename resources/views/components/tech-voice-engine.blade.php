@props([
    'defaultLang' => app()->getLocale() === 'ta' ? 'ta-IN' : 'en-IN'
])

{{-- TECHNICIAN VOICE DICTATION & SPEECH RECOGNITION ENGINE --}}
<style>
    /* Mic button styles */
    .tech-voice-btn {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        touch-action: manipulation;
    }
    .tech-voice-btn:hover {
        transform: translateY(-1px);
    }
    .tech-voice-btn:active {
        transform: translateY(0);
    }
    .tech-voice-btn.is-listening {
        background: linear-gradient(135deg, #e11d48, #be123c) !important;
        border-color: #f43f5e !important;
        color: #ffffff !important;
        box-shadow: 0 0 14px rgba(244, 63, 94, 0.55), 0 0 0 2px rgba(244, 63, 94, 0.3) !important;
        animation: techMicPulse 1.2s ease-in-out infinite alternate;
    }
    .tech-voice-btn.is-listening svg {
        color: #ffffff !important;
        stroke: #ffffff !important;
    }
    .tech-voice-btn.is-listening .tech-voice-btn-text {
        color: #ffffff !important;
    }

    @keyframes techMicPulse {
        0% { transform: scale(1); box-shadow: 0 0 10px rgba(244, 63, 94, 0.4); }
        100% { transform: scale(1.04); box-shadow: 0 0 18px rgba(244, 63, 94, 0.7); }
    }

    /* Active input field glowing border */
    .tech-voice-active-input {
        border-color: #f43f5e !important;
        box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.28) !important;
        background-color: rgba(254, 242, 242, 0.5) !important;
    }
    html.dark .tech-voice-active-input {
        background-color: rgba(69, 10, 10, 0.35) !important;
        border-color: #fb7185 !important;
        box-shadow: 0 0 0 3px rgba(251, 113, 133, 0.3) !important;
    }

    /* Floating Equalizer Sound Bars */
    .tech-eq-bar {
        width: 3px;
        background-color: #f43f5e;
        border-radius: 9999px;
        animation: techEqAnim 0.8s ease-in-out infinite alternate;
    }
    .tech-eq-bar:nth-child(1) { height: 8px; animation-delay: 0.1s; }
    .tech-eq-bar:nth-child(2) { height: 16px; animation-delay: 0.3s; }
    .tech-eq-bar:nth-child(3) { height: 22px; animation-delay: 0.05s; }
    .tech-eq-bar:nth-child(4) { height: 14px; animation-delay: 0.25s; }
    .tech-eq-bar:nth-child(5) { height: 9px; animation-delay: 0.4s; }

    @keyframes techEqAnim {
        0% { height: 4px; }
        100% { height: 20px; }
    }

    /* Floating FAB mic */
    .tech-fab-mic {
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .tech-fab-mic:hover {
        transform: scale(1.08) translateY(-2px);
    }
    .tech-fab-mic.is-active {
        background: linear-gradient(135deg, #e11d48, #be123c) !important;
        box-shadow: 0 10px 25px -5px rgba(225, 29, 72, 0.6) !important;
        animation: techMicPulse 1.2s ease-in-out infinite alternate;
    }
</style>

{{-- FLOATING VOICE DICTATION HUD (Appears when recording is active) --}}
<div id="tech-voice-hud" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[100] w-[94vw] max-w-lg bg-slate-900/95 dark:bg-[#0b1329]/95 backdrop-blur-md text-white rounded-2xl border border-slate-700/80 shadow-2xl p-3.5 space-y-2.5 transition-all">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
            {{-- Soundwave Equalizer --}}
            <div class="flex items-center gap-0.5 h-6 px-1.5 py-1 rounded-lg bg-rose-500/15 border border-rose-500/30 shrink-0">
                <div class="tech-eq-bar"></div>
                <div class="tech-eq-bar"></div>
                <div class="tech-eq-bar"></div>
                <div class="tech-eq-bar"></div>
                <div class="tech-eq-bar"></div>
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span class="text-xs font-bold text-rose-400 uppercase tracking-wider">{{ __('Listening… Speak Now') }}</span>
                </div>
                <p id="tech-voice-target-label" class="text-[11px] text-slate-300 truncate font-medium">--</p>
            </div>
        </div>

        {{-- Controls --}}
        <div class="flex items-center gap-1.5 shrink-0">
            {{-- Language Toggle --}}
            <button type="button" onclick="window.techVoice.toggleLang()" id="tech-voice-lang-btn" 
                    class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-600 text-[11px] font-bold text-amber-300 transition cursor-pointer"
                    title="{{ __('Toggle between English & Tamil speech recognition') }}">
                🌐 <span id="tech-voice-lang-label">EN</span>
            </button>

            {{-- Clear Button --}}
            <button type="button" onclick="window.techVoice.clearCurrentField()" 
                    class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-600 text-[11px] font-bold text-slate-300 hover:text-white transition cursor-pointer"
                    title="{{ __('Clear field text') }}">
                {{ __('Clear') }}
            </button>

            {{-- Stop / Done Button --}}
            <button type="button" onclick="window.techVoice.stop()" 
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition cursor-pointer"
                    title="{{ __('Stop listening and finish') }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                <span>{{ __('Done') }}</span>
            </button>
        </div>
    </div>

    {{-- Live Transcript Preview Strip --}}
    <div class="p-2 rounded-xl bg-black/40 border border-slate-800 text-xs font-sans text-slate-200 min-h-[34px] flex items-center justify-between">
        <span id="tech-voice-preview" class="italic text-slate-400">{{ __('Say something like customer name, address, or notes...') }}</span>
        <span class="text-[10px] text-slate-500 ml-2 shrink-0">{{ __('Live Auto-Typing') }}</span>
    </div>
</div>



<script>
(function() {
    // Check Web Speech API Support
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    class TechVoiceDictationEngine {
        constructor() {
            this.recognition = null;
            this.isListening = false;
            this.activeInput = null;
            this.activeBtn = null;
            this.initialValue = '';
            this.currentTranscript = '';
            this.currentLang = @js($defaultLang); // 'ta-IN' or 'en-IN'
            this.lastFocusedInput = null;
            this.audioCtx = null;
            this.mode = 'append'; // 'append' or 'replace'

            this.initListeners();
        }

        initListeners() {
            // Track last focused input on page so Floating FAB knows where to speak
            document.addEventListener('focusin', (e) => {
                if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
                    if (e.target.type !== 'file' && e.target.type !== 'checkbox' && e.target.type !== 'radio' && e.target.type !== 'hidden') {
                        this.lastFocusedInput = e.target;
                    }
                }
            });

            // Update language pill display
            this.updateLangBadge();
        }

        updateLangBadge() {
            const lbl = document.getElementById('tech-voice-lang-label');
            if (lbl) {
                lbl.textContent = this.currentLang.startsWith('ta') ? 'தமிழ்' : 'EN';
            }
        }

        toggleLang() {
            if (this.currentLang.startsWith('ta')) {
                this.currentLang = 'en-IN';
            } else {
                this.currentLang = 'ta-IN';
            }
            this.updateLangBadge();

            // If currently listening, seamlessly restart with new language
            if (this.isListening && this.recognition) {
                const target = this.activeInput;
                const btn = this.activeBtn;
                this.stop(false);
                setTimeout(() => {
                    this.dictate(target, btn, { mode: this.mode });
                }, 200);
            }
        }

        playTone(type = 'start') {
            try {
                if (!this.audioCtx) {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                
                if (type === 'start') {
                    osc.frequency.setValueAtTime(440, this.audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(880, this.audioCtx.currentTime + 0.12);
                    gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.15);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.15);
                } else {
                    osc.frequency.setValueAtTime(660, this.audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(330, this.audioCtx.currentTime + 0.12);
                    gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.15);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.15);
                }
            } catch (e) {
                // AudioContext not allowed or unsupported
            }
        }

        dictate(target, btn = null, options = {}) {
            if (!SpeechRecognition) {
                alert('Voice speech recognition is not supported in this browser. Please use Google Chrome, Edge, or Safari on iOS 14.5+.');
                return;
            }

            // Resolve target input element
            let targetEl = null;
            if (typeof target === 'string') {
                targetEl = document.querySelector(target);
            } else if (target instanceof HTMLElement) {
                targetEl = target;
            }

            if (!targetEl) {
                console.warn('TechVoiceDictation: Target field not found:', target);
                return;
            }

            // If already listening to THIS field, clicking again stops it
            if (this.isListening && this.activeInput === targetEl) {
                this.stop();
                return;
            }

            // If currently listening to ANOTHER field, stop that one first
            if (this.isListening) {
                this.stop(false);
            }

            this.activeInput = targetEl;
            this.activeBtn = btn;
            this.lastFocusedInput = targetEl;
            this.mode = options.mode || 'append';
            this.initialValue = targetEl.value || '';
            this.currentTranscript = '';

            // Detect field type & name
            const fieldName = targetEl.getAttribute('name') || targetEl.getAttribute('id') || '';
            const fieldType = targetEl.getAttribute('type') || '';
            const isPhone = fieldType === 'tel' || fieldName.includes('phone');
            const isNumber = fieldType === 'number' || fieldName.includes('distance') || fieldName.includes('rate') || fieldName.includes('amount') || fieldName.includes('camera') || fieldName.includes('cable');

            // Find label text for user-friendly HUD display
            let labelText = '';
            if (targetEl.id) {
                const lbl = document.querySelector(`label[for="${targetEl.id}"]`);
                if (lbl) labelText = lbl.textContent.trim();
            }
            if (!labelText) {
                const parentBox = targetEl.closest('div');
                const lbl = parentBox ? parentBox.querySelector('label') : null;
                if (lbl) labelText = lbl.textContent.trim();
            }
            if (!labelText) {
                labelText = targetEl.getAttribute('placeholder') || targetEl.getAttribute('aria-label') || fieldName || 'Active Input';
            }
            // Strip trailing asterisks or punctuation from label
            labelText = labelText.replace(/[*:]+$/, '').trim();

            try {
                this.recognition = new SpeechRecognition();
                this.recognition.continuous = true; // Stay active while user pauses between sentences
                this.recognition.interimResults = true;
                this.recognition.lang = this.currentLang;
                this.recognition.maxAlternatives = 1;

                this.recognition.onstart = () => {
                    this.isListening = true;
                    this.playTone('start');

                    // Highlight input field
                    this.activeInput.classList.add('tech-voice-active-input');
                    this.activeInput.focus();

                    // Animate trigger button
                    if (this.activeBtn) {
                        this.activeBtn.classList.add('is-listening');
                        const txt = this.activeBtn.querySelector('.tech-voice-btn-text');
                        if (txt) txt.textContent = 'Listening…';
                    }

                    // Animate Floating FAB
                    const fab = document.getElementById('tech-global-fab-mic');
                    if (fab) fab.classList.add('is-active');

                    // Show HUD
                    const hud = document.getElementById('tech-voice-hud');
                    const targetLabel = document.getElementById('tech-voice-target-label');
                    const preview = document.getElementById('tech-voice-preview');
                    if (hud) hud.classList.remove('hidden');
                    if (targetLabel) targetLabel.textContent = `Field: ${labelText}`;
                    if (preview) {
                        preview.textContent = 'Listening… Speak clearly into microphone';
                        preview.classList.add('text-amber-400');
                    }
                };

                this.recognition.onresult = (event) => {
                    let interimText = '';
                    let finalText = '';

                    for (let i = event.resultIndex; i < event.results.length; ++i) {
                        const transcript = event.results[i][0].transcript;
                        if (event.results[i].isFinal) {
                            finalText += transcript;
                        } else {
                            interimText += transcript;
                        }
                    }

                    let spokenText = (finalText || interimText).trim();

                    // If numeric or phone field, format spoken words to digits
                    if (isPhone || isNumber) {
                        spokenText = this.convertSpokenWordsToNumbers(spokenText, isPhone);
                    }

                    // Update live preview in HUD
                    const preview = document.getElementById('tech-voice-preview');
                    if (preview && spokenText) {
                        preview.textContent = `"${spokenText}"`;
                        preview.classList.remove('text-slate-400');
                        preview.classList.add('text-sky-300');
                    }

                    // Update input value
                    if (spokenText) {
                        if (this.mode === 'replace' || !this.initialValue) {
                            this.activeInput.value = spokenText;
                        } else {
                            // Smart append with clean spacing
                            const trimmedInit = this.initialValue.trim();
                            this.activeInput.value = `${trimmedInit} ${spokenText}`;
                        }

                        // Dispatch input & change events for live data bindings
                        this.activeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    if (finalText) {
                        // Update initialValue base so next spoken sentence appends cleanly
                        this.initialValue = this.activeInput.value;
                    }
                };

                this.recognition.onerror = (event) => {
                    console.warn('TechVoiceDictation Error:', event.error);
                    const preview = document.getElementById('tech-voice-preview');

                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        alert('Microphone permission is blocked in your browser. Please tap the lock / settings icon near your browser address bar and set Microphone to "Allow".');
                        this.stop();
                    } else if (event.error === 'no-speech') {
                        if (preview) {
                            preview.textContent = 'No voice detected. Tap mic to try again.';
                            preview.classList.add('text-rose-400');
                        }
                    } else {
                        if (preview) {
                            preview.textContent = `Voice recognition error (${event.error}). Please try again.`;
                        }
                    }
                };

                this.recognition.onend = () => {
                    // Only stop UI if user initiated stop or session completed
                    if (this.isListening) {
                        this.stop();
                    }
                };

                this.recognition.start();

            } catch (err) {
                console.error('TechVoiceDictation start exception:', err);
                this.stop();
            }
        }

        stop(playAudio = true) {
            if (playAudio && this.isListening) {
                this.playTone('stop');
            }

            this.isListening = false;

            if (this.recognition) {
                try {
                    this.recognition.stop();
                } catch (e) {}
                this.recognition = null;
            }

            // Clean up input highlight
            if (this.activeInput) {
                this.activeInput.classList.remove('tech-voice-active-input');
                this.activeInput.dispatchEvent(new Event('change', { bubbles: true }));
                this.activeInput = null;
            }

            // Reset trigger button
            if (this.activeBtn) {
                this.activeBtn.classList.remove('is-listening');
                const txt = this.activeBtn.querySelector('.tech-voice-btn-text');
                if (txt) txt.textContent = 'Speak';
                this.activeBtn = null;
            }

            // Reset Floating FAB
            const fab = document.getElementById('tech-global-fab-mic');
            if (fab) fab.classList.remove('is-active');

            // Hide HUD
            const hud = document.getElementById('tech-voice-hud');
            if (hud) hud.classList.add('hidden');
        }

        clearCurrentField() {
            if (this.activeInput) {
                this.activeInput.value = '';
                this.initialValue = '';
                this.activeInput.dispatchEvent(new Event('input', { bubbles: true }));
                this.activeInput.dispatchEvent(new Event('change', { bubbles: true }));

                const preview = document.getElementById('tech-voice-preview');
                if (preview) {
                    preview.textContent = 'Field cleared. Speak now…';
                }
            }
        }

        handleFabClick() {
            // If already listening, stop
            if (this.isListening) {
                this.stop();
                return;
            }

            // Find best target input:
            // 1. Currently focused or last focused input
            if (this.lastFocusedInput && document.body.contains(this.lastFocusedInput)) {
                this.dictate(this.lastFocusedInput, null);
                return;
            }

            // 2. Or first visible empty text input inside open modal
            const openModal = document.querySelector('[x-show]:not([style*="display: none"]), .modal:not(.hidden)');
            if (openModal) {
                const firstInput = openModal.querySelector('input:not([type=hidden]):not([type=checkbox]), textarea');
                if (firstInput) {
                    this.dictate(firstInput, null);
                    return;
                }
            }

            // 3. Or first visible form input on page
            const candidate = document.querySelector('#lead_customer_name, #tech-checkin-notes, #worker_name, input[type=text], textarea');
            if (candidate) {
                this.dictate(candidate, null);
            }
        }

        convertSpokenWordsToNumbers(text, isPhone = false) {
            let s = text.toLowerCase();

            // English and Tamil number dictionary
            const numberMap = {
                'double zero': '00', 'triple zero': '000',
                'zero': '0', 'oh': '0', 'one': '1', 'two': '2', 'to': '2', 'too': '2', 'three': '3', 
                'four': '4', 'for': '4', 'five': '5', 'six': '6', 'seven': '7', 'eight': '8', 
                'ate': '8', 'nine': '9',
                // Tamil spoken numbers
                'பூஜ்ஜியம்': '0', 'ஒன்று': '1', 'ஒன்னு': '1', 'இரண்டு': '2', 'ரெண்டு': '2',
                'மூன்று': '3', 'மூணு': '3', 'நான்கு': '4', 'நாலு': '4', 'ஐந்து': '5', 'அஞ்சு': '5',
                'ஆறு': '6', 'ஏழு': '7', 'எட்டு': '8', 'ஒன்பது': '9'
            };

            for (const [word, digit] of Object.entries(numberMap)) {
                const regex = new RegExp(`\\b${word}\\b`, 'gi');
                s = s.replace(regex, digit);
            }

            if (isPhone) {
                // Remove all spaces and non-digit characters for clean phone format
                s = s.replace(/[^0-9]/g, '');
            }

            return s;
        }
    }

    // Expose singleton to window
    window.techVoice = new TechVoiceDictationEngine();
})();
</script>
