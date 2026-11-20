<x-app-layout>
    <div class="space-y-4">
        {{-- Top Navigation & Action Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('quotations.show', $quotation) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>{{ __('Back to Quotation') }}</span>
                </a>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Quotation Preview #{{ $quotation->quotation_no }}</span>
                        @if($quotation->lead)
                            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">({{ $quotation->lead->customer_name }})</span>
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Total Amount: <strong class="text-slate-900 dark:text-white">₹{{ number_format(round((float)$quotation->total)) }}</strong> &bull; Total Items: <strong>{{ $quotation->items->count() }}</strong> &bull; Customer: {{ $quotation->lead?->customer_name ?? '—' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                {{-- Form to save currently inspected format & pricing mode as default --}}
                <form method="POST" action="{{ route('quotations.set-format', $quotation) }}" id="save-default-form" class="inline">
                    @csrf
                    <input type="hidden" name="pdf_format" id="save-default-format" value="{{ $format }}">
                    <input type="hidden" name="pricing_mode" id="save-default-mode" value="{{ $pricingMode }}">
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition shadow-2xs cursor-pointer"
                            title="{{ __('Save the selected layout format and pricing mode as the default for this quotation') }}">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ __('Save as Default') }}</span>
                    </button>
                </form>

                <a id="top-download-btn" 
                   href="{{ route('quotations.download-pdf', [$quotation, $format, 'pricing_mode' => $pricingMode]) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>{{ __('Download PDF') }}</span>
                </a>
            </div>
        </div>

        {{-- MAIN VIEWER CONTAINER WITH INTEGRATED INSTANT SWITCHER TABS DIRECTLY ABOVE VIEWER --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-xs overflow-hidden">
            
            {{-- TOOLBAR DIRECTLY ABOVE THE VIEWER --}}
            <div class="p-3 sm:p-3.5 bg-slate-50 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
                
                {{-- Left Group: Instant Format Switcher Tabs & Pricing Mode Toggle --}}
                <div class="flex flex-wrap items-center gap-3">
                    
                    {{-- 1. Format Switcher Tabs --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mr-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>{{ __('Layout:') }}</span>
                        </span>
                        <div class="inline-flex p-1 bg-slate-200/80 dark:bg-slate-700/80 rounded-xl border border-slate-300/80 dark:border-slate-600 text-xs font-semibold shadow-inner gap-0.5">
                            <button type="button" 
                                    onclick="selectFormat('1')" 
                                    id="tab-fmt-1"
                                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $format == '1' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-600/50' }}"
                                    title="Format 1: Precision IT Systems (Executive Modern)">
                                <span class="tab-indicator w-2 h-2 rounded-full bg-white {{ $format == '1' ? '' : 'hidden' }}"></span>
                                <span>Format 1 (Precision)</span>
                            </button>
                            <button type="button" 
                                    onclick="selectFormat('2')" 
                                    id="tab-fmt-2"
                                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $format == '2' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-600/50' }}"
                                    title="Format 2: NP Solutions (Technical Detailed)">
                                <span class="tab-indicator w-2 h-2 rounded-full bg-white {{ $format == '2' ? '' : 'hidden' }}"></span>
                                <span>Format 2 (NP Solutions)</span>
                            </button>
                            <button type="button" 
                                    onclick="selectFormat('3')" 
                                    id="tab-fmt-3"
                                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $format == '3' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-600/50' }}"
                                    title="Format 3: Linepix GST Invoice">
                                <span class="tab-indicator w-2 h-2 rounded-full bg-white {{ $format == '3' ? '' : 'hidden' }}"></span>
                                <span>Format 3 (Linepix)</span>
                            </button>
                        </div>
                    </div>

                    <div class="h-6 w-[1px] bg-slate-300 dark:bg-slate-600 hidden md:block"></div>

                    {{-- 2. Pricing Mode Switcher Tabs --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mr-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('Pricing Mode:') }}</span>
                        </span>
                        <div class="inline-flex p-1 bg-slate-200/80 dark:bg-slate-700/80 rounded-xl border border-slate-300/80 dark:border-slate-600 text-xs font-semibold shadow-inner gap-0.5">
                            <button type="button" 
                                    onclick="selectPricingMode('itemized')" 
                                    id="tab-mode-itemized"
                                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $pricingMode === 'itemized' ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-600/50' }}"
                                    title="{{ __('Show all individual product prices, unit rates, and totals') }}">
                                <span class="tab-indicator w-2 h-2 rounded-full bg-white {{ $pricingMode === 'itemized' ? '' : 'hidden' }}"></span>
                                <span>With Product Cost & Total</span>
                            </button>
                            <button type="button" 
                                    onclick="selectPricingMode('total_only')" 
                                    id="tab-mode-total_only"
                                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $pricingMode === 'total_only' ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-600/50' }}"
                                    title="{{ __('Hide individual line prices; show turnkey scope package and grand total only') }}">
                                <span class="tab-indicator w-2 h-2 rounded-full bg-white {{ $pricingMode === 'total_only' ? '' : 'hidden' }}"></span>
                                <span>Total Cost Only</span>
                            </button>
                        </div>
                    </div>

                </div>

                {{-- Right Group: Status Badge, Fullscreen & Direct Download --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span id="preview-active-badge" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hidden lg:inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                        <span id="preview-active-text">
                            @if($format == '1') F1 (Precision)
                            @elseif($format == '2') F2 (NP Solutions)
                            @elseif($format == '3') F3 (Linepix)
                            @endif
                            &bull; {{ $pricingMode === 'total_only' ? 'Total Cost Only' : 'With Product Cost' }}
                        </span>
                    </span>

                    <button type="button" 
                            onclick="toggleFullscreen()" 
                            class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition shadow-2xs flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        <span id="fullscreen-text">{{ __('Full Screen') }}</span>
                    </button>
                    
                    <a id="toolbar-download-btn" 
                       href="{{ route('quotations.download-pdf', [$quotation, $format, 'pricing_mode' => $pricingMode]) }}" 
                       class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 transition shadow-2xs flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>{{ __('Download') }}</span>
                    </a>
                </div>
            </div>

            {{-- LIVE FULL SIZE DOCUMENT VIEWER CONTAINER --}}
            <div id="live-iframe-wrapper" class="relative w-full bg-slate-100 dark:bg-slate-900" style="height: 980px;">
                <iframe id="live-quotation-iframe" 
                        src="{{ route('quotations.preview-pdf', [$quotation, $format, 'pricing_mode' => $pricingMode]) }}" 
                        class="w-full h-full border-none bg-white" 
                        title="Quotation PDF Live Document">
                </iframe>
            </div>
        </div>
    </div>

    <script>
    let currentFormat = '{{ $format }}';
    let currentPricingMode = '{{ $pricingMode }}';
    const quotationId = '{{ $quotation->id }}';

    const formatLabels = {
        '1': 'F1 (Precision)',
        '2': 'F2 (NP Solutions)',
        '3': 'F3 (Linepix)'
    };

    function selectFormat(fmt) {
        currentFormat = fmt;
        document.getElementById('save-default-format').value = fmt;

        // Update format tabs visual styles
        ['1', '2', '3'].forEach(f => {
            const tab = document.getElementById(`tab-fmt-${f}`);
            if (!tab) return;
            const dot = tab.querySelector('.tab-indicator');

            if (f === fmt) {
                tab.className = 'px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 bg-blue-600 text-white font-bold shadow-xs';
                if (dot) dot.classList.remove('hidden');
            } else {
                tab.className = 'px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-600/50';
                if (dot) dot.classList.add('hidden');
            }
        });

        reloadIframePreview();
    }

    function selectPricingMode(mode) {
        currentPricingMode = mode;
        document.getElementById('save-default-mode').value = mode;

        // Update pricing mode tabs visual styles
        ['itemized', 'total_only'].forEach(m => {
            const tab = document.getElementById(`tab-mode-${m}`);
            if (!tab) return;
            const dot = tab.querySelector('.tab-indicator');

            if (m === mode) {
                tab.className = 'px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 bg-amber-600 text-white font-bold shadow-xs';
                if (dot) dot.classList.remove('hidden');
            } else {
                tab.className = 'px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-600/50';
                if (dot) dot.classList.add('hidden');
            }
        });

        reloadIframePreview();
    }

    function reloadIframePreview() {
        const iframe = document.getElementById('live-quotation-iframe');
        const activeText = document.getElementById('preview-active-text');
        const topDl = document.getElementById('top-download-btn');
        const toolbarDl = document.getElementById('toolbar-download-btn');

        const newPreviewUrl = `/quotations/${quotationId}/preview-pdf/${currentFormat}?pricing_mode=${encodeURIComponent(currentPricingMode)}`;
        const newDlUrl = `/quotations/${quotationId}/download-pdf/${currentFormat}?pricing_mode=${encodeURIComponent(currentPricingMode)}`;

        if (iframe) {
            iframe.src = newPreviewUrl;
        }

        if (activeText) {
            const fmtName = formatLabels[currentFormat] || `Format ${currentFormat}`;
            const modeName = currentPricingMode === 'total_only' ? 'Total Cost Only' : 'With Product Cost';
            activeText.innerText = `${fmtName} • ${modeName}`;
        }

        if (topDl) topDl.href = newDlUrl;
        if (toolbarDl) toolbarDl.href = newDlUrl;

        // Update browser URL query without reloading the page
        const newPath = `/quotations/${quotationId}/preview/${currentFormat}?pricing_mode=${encodeURIComponent(currentPricingMode)}`;
        window.history.replaceState({}, '', newPath);
    }

    function toggleFullscreen() {
        const wrapper = document.getElementById('live-iframe-wrapper');
        const text = document.getElementById('fullscreen-text');
        if (!wrapper) return;

        if (!document.fullscreenElement) {
            if (wrapper.requestFullscreen) wrapper.requestFullscreen();
            else if (wrapper.webkitRequestFullscreen) wrapper.webkitRequestFullscreen();
            if (text) text.innerText = 'Exit Fullscreen';
        } else {
            if (document.exitFullscreen) document.exitFullscreen();
            if (text) text.innerText = 'Full Screen';
        }
    }
    </script>
</x-app-layout>
