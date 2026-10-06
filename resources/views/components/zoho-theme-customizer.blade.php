@props([
    'inline' => true,
    'userId' => null,
    'userAccent' => null,
    'userMode' => null,
    'userStyle' => null,
])

{{-- Zoho CRM Inspired Mode & Theme Customizer Component --}}
<div x-data="zohoThemeManager({
    userId: {{ json_encode($userId ?? (auth()->check() ? auth()->id() : null)) }},
    userAccent: {{ json_encode($userAccent ?? (auth()->check() ? auth()->user()->theme_accent : null)) }},
    userMode: {{ json_encode($userMode ?? (auth()->check() ? auth()->user()->theme_mode : null)) }},
    userStyle: {{ json_encode($userStyle ?? (auth()->check() ? auth()->user()->theme_style : null)) }}
})" x-init="init()" class="w-full select-none text-slate-800 dark:text-slate-100">
    <div class="w-full text-slate-800 dark:text-slate-100">

        {{-- Section 1: Mode --}}
        <div class="mb-3.5">
            <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-2 font-heading tracking-tight flex items-center justify-between">
                <span>Mode</span>
                <span class="text-[10px] font-semibold text-slate-400 capitalize" x-text="mode === 'auto' ? 'Auto (System)' : mode"></span>
            </h4>

            {{-- Mode Segmented Pill Buttons (Matches Zoho CRM UI) --}}
            <div class="p-1 bg-slate-100 dark:bg-slate-800/90 rounded-full flex items-center justify-between gap-1 border border-slate-200/60 dark:border-slate-700/60">
                {{-- Day Option --}}
                <button type="button" 
                        @click="setMode('day')" 
                        class="flex-1 py-1.5 px-2.5 rounded-full text-xs font-bold flex items-center justify-center gap-1.5 transition-all duration-200 cursor-pointer"
                        :style="mode === 'day' ? { backgroundColor: accent, color: '#ffffff', boxShadow: '0 2px 8px -1px ' + accent + '80' } : {}"
                        :class="mode !== 'day' ? 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' : ''">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="4"/>
                        <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41m14.14-14.14l-1.41 1.41"/>
                    </svg>
                    <span>Day</span>
                </button>

                {{-- Night Option --}}
                <button type="button" 
                        @click="setMode('night')" 
                        class="flex-1 py-1.5 px-2.5 rounded-full text-xs font-bold flex items-center justify-center gap-1.5 transition-all duration-200 cursor-pointer"
                        :style="mode === 'night' ? { backgroundColor: accent, color: '#ffffff', boxShadow: '0 2px 8px -1px ' + accent + '80' } : {}"
                        :class="mode !== 'night' ? 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' : ''">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <span>Night</span>
                </button>

                {{-- Auto Option --}}
                <button type="button" 
                        @click="setMode('auto')" 
                        class="flex-1 py-1.5 px-2.5 rounded-full text-xs font-bold flex items-center justify-center gap-1.5 transition-all duration-200 cursor-pointer relative group/auto"
                        :style="mode === 'auto' ? { backgroundColor: accent, color: '#ffffff', boxShadow: '0 2px 8px -1px ' + accent + '80' } : {}"
                        :class="mode !== 'auto' ? 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' : ''"
                        title="Automatically syncs with your device / browser system setting">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    <span>Auto</span>
                </button>
            </div>
        </div>

        {{-- Section 2: Themes (Dark vs Lite) --}}
        <div class="mb-3 pt-2 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-900 dark:text-white font-heading">Header Theme</span>

                <div class="flex items-center gap-3 text-xs">
                    {{-- Dark Theme Radio --}}
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold text-slate-700 dark:text-slate-300 select-none">
                        <input type="radio" 
                               name="{{ $inline ? 'crm_theme_style_inline_' . uniqid() : 'crm_theme_style' }}" 
                               value="dark" 
                               :checked="themeStyle === 'dark'"
                               @change="setThemeStyle('dark')"
                               class="sr-only">
                        <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                              :class="themeStyle === 'dark' ? 'border-2' : 'border-slate-300 dark:border-slate-600'"
                              :style="themeStyle === 'dark' ? { borderColor: accent } : {}">
                            <span x-show="themeStyle === 'dark'" class="w-2 h-2 rounded-full" :style="{ backgroundColor: accent }"></span>
                        </span>
                        <span>Dark</span>
                    </label>

                    {{-- Lite Theme Radio --}}
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold text-slate-700 dark:text-slate-300 select-none">
                        <input type="radio" 
                               name="{{ $inline ? 'crm_theme_style_inline_' . uniqid() : 'crm_theme_style' }}" 
                               value="lite" 
                               :checked="themeStyle === 'lite'"
                               @change="setThemeStyle('lite')"
                               class="sr-only">
                        <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                              :class="themeStyle === 'lite' ? 'border-2' : 'border-slate-300 dark:border-slate-600'"
                              :style="themeStyle === 'lite' ? { borderColor: accent } : {}">
                            <span x-show="themeStyle === 'lite'" class="w-2 h-2 rounded-full" :style="{ backgroundColor: accent }"></span>
                        </span>
                        <span>Lite</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 3: Accent Colors Palette (Exact 13 Zoho CRM Colors) --}}
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2 flex items-center justify-between">
                <span>Accent Palette</span>
                <span class="text-[9px] font-mono text-slate-400 lowercase" x-text="accent"></span>
            </div>
            
            <div class="grid grid-cols-7 gap-1.5 sm:gap-2 items-center justify-between">
                <template x-for="c in colors" :key="c.hex">
                    <button type="button" 
                            @click="setAccent(c.hex)" 
                            :title="c.name"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition-transform hover:scale-115 active:scale-95 cursor-pointer shadow-xs focus:outline-none ring-offset-2 ring-offset-white dark:ring-offset-[#0f172a]"
                            :style="{ backgroundColor: c.hex }"
                            :class="accent.toLowerCase() === c.hex.toLowerCase() ? 'ring-2 ring-slate-900 dark:ring-white scale-105' : ''">
                        {{-- White Checkmark on Selected Swatch --}}
                        <svg x-show="accent.toLowerCase() === c.hex.toLowerCase()" class="w-3.5 h-3.5 text-white filter drop-shadow-xs" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
window.zohoThemeManager = function(cfg = {}) {
    const currentUserId = cfg.userId || window.crmCurrentUserId || null;
    const accentKey = currentUserId ? ('crm_accent_user_' + currentUserId) : 'crm_accent';
    const modeKey = currentUserId ? ('crm_mode_user_' + currentUserId) : 'crm_mode';
    const styleKey = currentUserId ? ('crm_theme_style_user_' + currentUserId) : 'crm_theme_style';

    let storedMode = null;
    let storedStyle = null;
    let storedAccent = null;
    try {
        storedMode = localStorage.getItem(modeKey);
        storedStyle = localStorage.getItem(styleKey);
        storedAccent = localStorage.getItem(accentKey);
    } catch(e) {}

    const initialMode = storedMode || cfg.userMode || 'auto';
    const initialStyle = storedStyle || cfg.userStyle || 'dark';
    const initialAccent = storedAccent || cfg.userAccent || '#2563eb';

    return {
        userId: currentUserId,
        accentKey: accentKey,
        modeKey: modeKey,
        styleKey: styleKey,
        open: false,
        mode: initialMode,
        themeStyle: initialStyle,
        accent: initialAccent,
        colors: [
            { name: 'Royal Blue', hex: '#2563eb' },
            { name: 'Navy', hex: '#1e293b' },
            { name: 'Teal', hex: '#0d9488' },
            { name: 'Forest Green', hex: '#16a34a' },
            { name: 'Olive Green', hex: '#65a30d' },
            { name: 'Amber Gold', hex: '#d97706' },
            { name: 'Terracotta Rust', hex: '#c2410c' },
            { name: 'Ruby Red', hex: '#be123c' },
            { name: 'Plum Magenta', hex: '#831843' },
            { name: 'Deep Purple', hex: '#581c87' },
            { name: 'Indigo', hex: '#4338ca' },
            { name: 'Ocean Blue', hex: '#0284c7' },
            { name: 'Espresso Brown', hex: '#78350f' }
        ],
        init() {
            this.applyTheme();
            
            // Watch for external theme-changed events strictly for this user
            window.addEventListener('theme-changed', (e) => {
                if (e.detail && (!e.detail.userId || e.detail.userId == this.userId)) {
                    if (e.detail.mode) this.mode = e.detail.mode;
                    if (e.detail.accent) this.accent = e.detail.accent;
                    if (e.detail.themeStyle) this.themeStyle = e.detail.themeStyle;
                }
            });

            // Watch for OS preference change when in 'auto' mode
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', () => {
                if (this.mode === 'auto') {
                    this.applyTheme();
                }
            });
        },
        setMode(newMode) {
            this.mode = newMode;
            try { localStorage.setItem(this.modeKey, newMode); } catch(e) {}
            this.applyTheme();
            this.persistToDatabase();
        },
        setThemeStyle(newStyle) {
            this.themeStyle = newStyle;
            try { localStorage.setItem(this.styleKey, newStyle); } catch(e) {}
            this.applyTheme();
            this.persistToDatabase();
        },
        setAccent(hex) {
            this.accent = hex;
            try { localStorage.setItem(this.accentKey, hex); } catch(e) {}
            this.applyTheme();
            this.persistToDatabase();
        },
        async persistToDatabase() {
            if (!this.userId) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                await fetch('/profile/theme', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        accent: this.accent,
                        mode: this.mode,
                        themeStyle: this.themeStyle
                    })
                });
            } catch(e) {
                console.warn('Persist theme preference failed:', e);
            }
        },
        applyTheme() {
            const isDark = (this.mode === 'night') || 
                           (this.mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            document.documentElement.setAttribute('data-header-theme', this.themeStyle);
            document.documentElement.style.setProperty('--crm-accent', this.accent);
            
            if (typeof window.applyGlobalCrmAccent === 'function') {
                window.applyGlobalCrmAccent(this.accent);
            }
            
            // Dispatch event for any real-time UI subscribers, tagged with this.userId
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { 
                    userId: this.userId,
                    isDark: isDark, 
                    mode: this.mode, 
                    themeStyle: this.themeStyle, 
                    accent: this.accent 
                }
            }));
        }
    };
};
</script>
