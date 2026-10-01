@props(['inline' => false])

{{-- Zoho CRM Inspired Mode & Theme Customizer Component --}}
<div x-data="zohoThemeManager()" x-init="init()" class="{{ $inline ? 'w-full select-none text-slate-800 dark:text-slate-100' : 'relative shrink-0' }}" @click.away="open = false">
    @if(!$inline)
        {{-- Trigger Button in Top Header (when used as popover) --}}
        <button type="button" 
                @click="open = !open" 
                class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800 transition relative flex items-center justify-center cursor-pointer shrink-0 shadow-2xs group"
                :title="'Display & Theme Preferences (Current: ' + mode.toUpperCase() + ')'"
                aria-label="Display and theme settings">
            
            {{-- Palette Icon --}}
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>

            {{-- Active Accent Dot Indicator --}}
            <span class="absolute bottom-1 right-1 w-2.5 h-2.5 rounded-full ring-2 ring-white dark:ring-slate-900 shadow-2xs transition-colors"
                  :style="{ backgroundColor: accent }"></span>
        </button>
    @endif

    {{-- Content Panel (Popover if !$inline, direct block if $inline) --}}
    <div @if(!$inline)
            x-show="open" 
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
            x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
            class="absolute right-0 mt-2.5 w-76 sm:w-80 bg-white dark:bg-[#0f172a] rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-4 z-50 select-none text-slate-800 dark:text-slate-100"
            style="display: none;"
         @else
            class="w-full text-slate-800 dark:text-slate-100"
         @endif>

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

        @if(!$inline)
            {{-- Bottom Status Note --}}
            <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10px] text-slate-400">
                <span>Instant sync enabled</span>
                <span class="font-mono text-slate-500" x-text="accent"></span>
            </div>
        @endif

    </div>
</div>

<script>
if (typeof window.zohoThemeManager !== 'function') {
    window.zohoThemeManager = function() {
        return {
            open: false,
            mode: localStorage.getItem('crm_mode') || 'auto',
            themeStyle: localStorage.getItem('crm_theme_style') || 'dark',
            accent: localStorage.getItem('crm_accent') || '#be123c', // Default Ruby Red
            colors: [
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
                { name: 'Royal Blue', hex: '#2563eb' },
                { name: 'Ocean Blue', hex: '#0284c7' },
                { name: 'Espresso Brown', hex: '#78350f' }
            ],
            init() {
                this.applyTheme();
                
                // Watch for external theme-changed events
                window.addEventListener('theme-changed', (e) => {
                    if (e.detail) {
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
                localStorage.setItem('crm_mode', newMode);
                this.applyTheme();
            },
            setThemeStyle(newStyle) {
                this.themeStyle = newStyle;
                localStorage.setItem('crm_theme_style', newStyle);
                this.applyTheme();
            },
            setAccent(hex) {
                this.accent = hex;
                localStorage.setItem('crm_accent', hex);
                this.applyTheme();
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
                
                // Dispatch event for any real-time UI subscribers
                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: { isDark: isDark, mode: this.mode, themeStyle: this.themeStyle, accent: this.accent }
                }));
            }
        };
    };
}
</script>
