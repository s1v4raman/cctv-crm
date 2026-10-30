@props([
    'userId' => null,
    'userAccent' => null,
    'userMode' => null,
    'userStyle' => null,
])
{{-- Universal Realtime CRM Theme & Dynamic Logo Recoloring Engine --}}
<script>
(function() {
    // 1. Instant Synchronous Early Theme Hydration before any CSS or DOM rendering
    try {
        let early = null;
        @if(auth()->check())
            early = localStorage.getItem('crm_mode_user_{{ auth()->id() }}');
        @endif
        if (!early) early = localStorage.getItem('crm_mode');
        if (!early) early = localStorage.getItem('theme');
        if (!early) {
            const m = document.cookie.match('(^|;) ?crm_mode=([^;]*)(;|$)');
            if (m) early = decodeURIComponent(m[2]);
        }
        if (!early) {
            early = {!! json_encode($userMode ?? (auth()->check() ? auth()->user()->theme_mode : null)) !!};
        }

        if (early === 'night' || early === 'dark') {
            document.documentElement.classList.add('dark'); document.documentElement.setAttribute('data-theme', 'dark');
        } else if (early === 'day' || early === 'light') {
            document.documentElement.classList.remove('dark'); document.documentElement.setAttribute('data-theme', 'light');
        } else if (early === 'auto') {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark'); document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark'); document.documentElement.setAttribute('data-theme', 'light');
            }
        }
    } catch(e) {}

    window.crmLogoCache = window.crmLogoCache || {};

    const currentUserId = {!! json_encode($userId ?? (auth()->check() ? auth()->id() : null)) !!};
    const dbAccent = {!! json_encode($userAccent ?? (auth()->check() ? auth()->user()->theme_accent : null)) !!} || '#2563eb';
    const dbMode = {!! json_encode($userMode ?? (auth()->check() ? auth()->user()->theme_mode : null)) !!} || 'night';
    const dbStyle = {!! json_encode($userStyle ?? (auth()->check() ? auth()->user()->theme_style : null)) !!} || 'lite';

    // Scoped storage keys per user to guarantee absolute isolation between users
    const accentKey = currentUserId ? ('crm_accent_user_' + currentUserId) : 'crm_accent';
    const modeKey = currentUserId ? ('crm_mode_user_' + currentUserId) : 'crm_mode';
    const styleKey = currentUserId ? ('crm_theme_style_user_' + currentUserId) : 'crm_theme_style';

    window.crmCurrentUserId = currentUserId;
    window.crmAccentStorageKey = accentKey;
    window.crmModeStorageKey = modeKey;
    window.crmStyleStorageKey = styleKey;
    window.crmUserDefaultAccent = dbAccent;

    // Official Corporate Brand Logo Manager for Precision IT Systems
    window.recolorCrmBrandLogo = function(hex) {
        // Keep official Precision IT Systems logo intact across all theme accents.
        // Update favicon link to official logo if present.
        const favicon = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
        if (favicon && !favicon.href.includes('favicon.ico') && !favicon.href.includes('favicon.png')) {
            favicon.href = '/favicon.ico';
        }
    };

    // Universal Global Accent Injector
    window.applyGlobalCrmAccent = function(hex) {
        if (!hex) hex = dbAccent || '#2563eb';
        
        let hoverHex = hex;
        let tr = 37, tg = 99, tb = 235;
        try {
            let clean = hex.replace('#', '');
            if (clean.length === 3) clean = clean.split('').map(function(c) { return c + c; }).join('');
            let num = parseInt(clean, 16);
            tr = (num >> 16) & 255;
            tg = (num >> 8) & 255;
            tb = num & 255;
            let hr = Math.max(0, tr - 25);
            let hg = Math.max(0, tg - 25);
            let hb = Math.max(0, tb - 25);
            hoverHex = '#' + ((1 << 24) + (hr << 16) + (hg << 8) + hb).toString(16).slice(1);
        } catch(e) {}

        document.documentElement.style.setProperty('--crm-accent', hex);
        document.documentElement.style.setProperty('--crm-accent-hover', hoverHex);
        document.documentElement.style.setProperty('--crm-accent-rgb', tr + ', ' + tg + ', ' + tb);

        let styleEl = document.getElementById('crm-dynamic-theme-overrides');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'crm-dynamic-theme-overrides';
        }
        if (document.body) {
            document.body.appendChild(styleEl);
        } else if (document.head) {
            document.head.appendChild(styleEl);
        }

        styleEl.textContent = `
            :root {
                --crm-accent: ${hex} !important;
                --crm-accent-hover: ${hoverHex} !important;
                --crm-accent-shadow: rgba(${tr}, ${tg}, ${tb}, 0.25) !important;
                --crm-accent-rgb: ${tr}, ${tg}, ${tb} !important;
                --crm-accent-soft: rgba(${tr}, ${tg}, ${tb}, 0.12) !important;
                --crm-accent-border: rgba(${tr}, ${tg}, ${tb}, 0.28) !important;
                --brand-blue: ${hex} !important;
                --brand-blue-hover: ${hoverHex} !important;
                --primary-btn-bg: ${hex} !important;
                --color-blue-600: ${hex} !important;
                --color-blue-700: ${hoverHex} !important;
                --color-indigo-600: ${hex} !important;
                --color-indigo-700: ${hoverHex} !important;
                --ld-accent: ${hex} !important;
                --ld-accent-hover: ${hoverHex} !important;
                --ld-accent-soft: rgba(${tr}, ${tg}, ${tb}, 0.14) !important;
            }

            /* Brand Logo: PRECISION wordmark mask dynamically recolored */
            .crm-brand-logo .crm-logo-prec-mask {
                background-color: ${hex} !important;
            }
            
            /* 1. BRAND WORDMARK: Precision IT [Systems] */
            .crm-brand-accent-text,
            .crm-brand-accent-text * {
                color: ${hex} !important;
            }

            /* 2. USER AVATAR & HEADER BADGES (Clean solid flat single color) */
            .crm-user-avatar-badge,
            .crm-user-avatar,
            .crm-brand-tile,
            button[aria-label="User account menu"] {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                color: #ffffff !important;
                box-shadow: none !important;
            }

            /* Clean Outline Icon System: Proper size, linear stroke, no color boxes or shades */
            aside nav svg,
            .stat-card svg {
                stroke-width: 1.8 !important;
            }
            .card-icon,
            .crm-customer-icon-box {
                background: transparent !important;
                background-color: transparent !important;
                border: none !important;
                box-shadow: none !important;
                color: #475569 !important;
            }
            html.dark .card-icon,
            html.dark .crm-customer-icon-box {
                background: transparent !important;
                background-color: transparent !important;
                border: none !important;
                box-shadow: none !important;
                color: #94a3b8 !important;
            }

            /* 4. ALL MODULE PRIMARY ACTION BUTTONS (+ New, Create, Schedule, Save, Submit, Checkout) */
            button.bg-blue-600,
            button[class*="bg-blue-600"],
            a.bg-blue-600,
            a[class*="bg-blue-600"],
            button[class*="bg-[#2563eb]"],
            a[class*="bg-[#2563eb]"],
            button[class*="bg-blue-700"],
            a[class*="bg-blue-700"],
            .btn-primary,
            button.btn-primary,
            a.btn-primary,
            .btn-blue,
            button.btn-blue,
            a.btn-blue,
            .btn-amber,
            button.btn-amber,
            a.btn-amber,
            .crm-btn-primary,
            button.crm-btn-primary,
            a.crm-btn-primary,
            .crm-customer-action-btn,
            button.crm-customer-action-btn,
            a.crm-customer-action-btn,
            .crm-customer-header-action,
            a.crm-customer-header-action,
            .crm-hub-primary-btn,
            button.crm-hub-primary-btn,
            a.crm-hub-primary-btn,
            .modal-confirm-btn,
            button.btn-confirm,
            a[href*="export-pdf"].btn-primary,
            a.btn-primary,
            a.crm-btn-primary,
            a[href*="payment/checkout"],
            button[onclick*="siteSurveyBookingModal"],
            button[onclick*="amcRenewalModal"] {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border: 1px solid ${hex} !important;
                color: #ffffff !important;
                box-shadow: none !important;
            }

            .btn-primary *:not(svg):not(path),
            .crm-btn-primary *:not(svg):not(path),
            .btn-amber *:not(svg):not(path),
            button.bg-blue-600 *:not(svg):not(path),
            a.bg-blue-600 *:not(svg):not(path),
            button[class*="bg-blue-600"] *:not(svg):not(path),
            a[class*="bg-blue-600"] *:not(svg):not(path) {
                color: #ffffff !important;
            }

            /* Action button icons: strictly black color icons with fill: none in LIGHT MODE */
            html:not(.dark) button svg,
            html:not(.dark) a.btn-primary svg,
            html:not(.dark) a.btn-secondary svg,
            html:not(.dark) a.btn svg,
            html:not(.dark) a[class*="btn-"] svg,
            html:not(.dark) .btn-primary svg,
            html:not(.dark) .btn-secondary svg,
            html:not(.dark) .crm-btn-primary svg,
            html:not(.dark) button.bg-blue-600 svg,
            html:not(.dark) a.bg-blue-600 svg,
            html:not(.dark) button[class*="bg-blue-600"] svg,
            html:not(.dark) a[class*="bg-blue-600"] svg,
            html:not(.dark) .tech-card a svg,
            html:not(.dark) .tech-card button svg,
            html:not(.dark) .crm-label svg,
            html:not(.dark) label svg {
                stroke: #000000 !important;
                color: #000000 !important;
                fill: none !important;
            }
            html:not(.dark) button svg path,
            html:not(.dark) button svg circle,
            html:not(.dark) button svg rect,
            html:not(.dark) button svg line,
            html:not(.dark) button svg polyline,
            html:not(.dark) button svg polygon,
            html:not(.dark) a.btn-primary svg path,
            html:not(.dark) a.btn-secondary svg path,
            html:not(.dark) a[class*="btn-"] svg path,
            html:not(.dark) .tech-card a svg path,
            html:not(.dark) .tech-card button svg path,
            html:not(.dark) .crm-label svg path,
            html:not(.dark) .crm-label svg circle,
            html:not(.dark) .crm-label svg rect,
            html:not(.dark) .crm-label svg line,
            html:not(.dark) label svg path,
            html:not(.dark) label svg circle {
                fill: none !important;
                stroke: #000000 !important;
            }

            /* Dark Mode Button Icons: Clean Luminous White & Light Slate (Never invisible black) */
            html.dark .btn-primary svg,
            html.dark button.bg-blue-600 svg,
            html.dark a.bg-blue-600 svg,
            html.dark button[class*="bg-blue-600"] svg,
            html.dark a[class*="bg-blue-600"] svg,
            html.dark .btn-primary-login svg,
            html.dark button[type="submit"] svg {
                stroke: #ffffff !important;
                color: #ffffff !important;
                fill: none !important;
            }
            html.dark .btn-primary svg *,
            html.dark button.bg-blue-600 svg *,
            html.dark a.bg-blue-600 svg *,
            html.dark .btn-primary-login svg * {
                stroke: #ffffff !important;
                fill: none !important;
            }

            html.dark .btn-secondary svg,
            html.dark .tab-btn svg,
            html.dark .tech-card a svg,
            html.dark .tech-card button svg,
            html.dark a.btn-secondary svg,
            html.dark .crm-label svg,
            html.dark label svg {
                stroke: #ffffff !important;
                color: #ffffff !important;
                fill: none !important;
            }
            html.dark .btn-secondary svg *,
            html.dark .tab-btn svg *,
            html.dark .tech-card a svg *,
            html.dark .tech-card button svg *,
            html.dark .crm-label svg *,
            html.dark label svg * {
                stroke: #ffffff !important;
                fill: none !important;
            }

            html.dark .theme-toggle svg {
                stroke: #fbbf24 !important;
                color: #fbbf24 !important;
            }
            html.dark .theme-toggle svg circle {
                fill: #fbbf24 !important;
                stroke: #fbbf24 !important;
            }

            /* Universal Center-Aligned Button Fonts & Directional Border Guards */
            .btn-secondary,
            .crm-btn-secondary,
            a.btn-secondary,
            .btn-primary,
            .crm-btn-primary,
            a.btn-primary {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
                gap: 0.5rem !important;
            }
            .border-b { border-bottom-width: 1px !important; border-top-width: 0 !important; border-left-width: 0 !important; border-right-width: 0 !important; }
            .border-t { border-top-width: 1px !important; border-bottom-width: 0 !important; border-left-width: 0 !important; border-right-width: 0 !important; }
            .border-l { border-left-width: 1px !important; border-top-width: 0 !important; border-bottom-width: 0 !important; border-right-width: 0 !important; }
            .border-r { border-right-width: 1px !important; border-top-width: 0 !important; border-bottom-width: 0 !important; border-left-width: 0 !important; }
            .border-0, .border-none { border-width: 0 !important; }

            /* Universal Strict SVG Dimension Guards (Never allow oversized SVGs) */
            label svg,
            .crm-label svg,
            form label svg,
            .form-group label svg {
                width: 14px !important;
                height: 14px !important;
                max-width: 14px !important;
                max-height: 14px !important;
                min-width: 14px !important;
                min-height: 14px !important;
                display: inline-block !important;
                vertical-align: -2px !important;
                flex-shrink: 0 !important;
            }
            button:not(.crm-brand-tile):not([aria-label="User account menu"]) svg,
            a.btn-secondary svg,
            a.btn-primary svg,
            .btn-secondary svg,
            .btn-primary svg,
            .crm-btn-primary svg,
            .crm-btn-secondary svg {
                width: 15px !important;
                height: 15px !important;
                max-width: 15px !important;
                max-height: 15px !important;
                min-width: 15px !important;
                min-height: 15px !important;
                flex-shrink: 0 !important;
            }
            /* Universal Modal SVG and Container View Guards */
            [x-show*="modal"] svg:not(.crm-brand-tile),
            [x-show*="Modal"] svg:not(.crm-brand-tile),
            .modal-container svg:not(.crm-brand-tile) {
                max-width: 24px !important;
                max-height: 24px !important;
            }
            [x-show*="Modal"] div:not([class*="rounded-2xl"]):not([class*="rounded-xl"]):not([class*="rounded-lg"]):not(.crm-input):not(.ts-control):not(.ts-dropdown):not([class*="border-b"]):not([class*="border-t"]):not([class*="border-l"]):not([class*="border-r"]):not(input):not(select):not(textarea):not(button):not(a),
            [x-show*="modal"] div:not([class*="rounded-2xl"]):not([class*="rounded-xl"]):not([class*="rounded-lg"]):not(.crm-input):not(.ts-control):not(.ts-dropdown):not([class*="border-b"]):not([class*="border-t"]):not([class*="border-l"]):not([class*="border-r"]):not(input):not(select):not(textarea):not(button):not(a) {
                border-width: 0 !important;
            }

            button.bg-blue-600:hover,
            button[class*="bg-blue-600"]:hover,
            a.bg-blue-600:hover,
            a[class*="bg-blue-600"]:hover,
            button[class*="bg-[#2563eb]"]:hover,
            a[class*="bg-[#2563eb]"]:hover,
            .btn-primary:hover,
            button.btn-primary:hover,
            a.btn-primary:hover,
            .btn-blue:hover,
            button.btn-blue:hover,
            a.btn-blue:hover,
            .btn-amber:hover,
            button.btn-amber:hover,
            a.btn-amber:hover,
            .crm-btn-primary:hover,
            button.crm-btn-primary:hover,
            a.crm-btn-primary:hover,
            .crm-customer-action-btn:hover,
            a.crm-customer-action-btn:hover,
            .crm-customer-header-action:hover,
            a.crm-customer-header-action:hover,
            .crm-hub-primary-btn:hover,
            a.crm-hub-primary-btn:hover,
            .modal-confirm-btn:hover,
            button.btn-confirm:hover,
            a[href*="payment/checkout"]:hover {
                background: ${hoverHex} !important;
                background-color: ${hoverHex} !important;
                background-image: none !important;
                border-color: ${hoverHex} !important;
                color: #ffffff !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            }

            /* Dedicated Clean Helper & Module Navigation Buttons (Add New Customer, Add New Technician) */
            .btn-subtle,
            a.btn-subtle,
            button.btn-subtle,
            a[class*="btn-subtle"],
            button[class*="btn-subtle"],
            .btn-inline-add,
            a.btn-inline-add,
            button.btn-inline-add {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border: 1px solid ${hex} !important;
                color: #ffffff !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 14px !important;
                height: 32px !important;
                min-height: 32px !important;
                max-height: 32px !important;
                border-radius: 8px !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                line-height: 1 !important;
                text-decoration: none !important;
                white-space: nowrap !important;
                transition: all 0.15s ease !important;
                box-shadow: none !important;
                cursor: pointer !important;
            }
            .btn-subtle:hover,
            a.btn-subtle:hover,
            button.btn-subtle:hover,
            a[class*="btn-subtle"]:hover,
            button[class*="btn-subtle"]:hover,
            .btn-inline-add:hover,
            a.btn-inline-add:hover,
            button.btn-inline-add:hover {
                background: ${hoverHex} !important;
                background-color: ${hoverHex} !important;
                color: #ffffff !important;
                border-color: ${hoverHex} !important;
            }
            .btn-subtle *,
            a.btn-subtle *,
            button.btn-subtle *,
            a[class*="btn-subtle"] *,
            button[class*="btn-subtle"] *,
            .btn-inline-add *,
            a.btn-inline-add *,
            button.btn-inline-add * {
                color: #ffffff !important;
                font-weight: 600 !important;
            }


            /* 5. ALL MODULE SEARCH & FILTER BUTTONS */
            .btn-search,
            button.btn-search,
            .btn-filter,
            button.btn-filter,
            a.btn-filter,
            .btn-adjust,
            button.btn-adjust,
            a.btn-adjust,
            button[onclick*="search"],
            button[onclick*="Search"],
            button[onclick*="filter"],
            button[onclick*="Filter"],
            button[onclick*="filterLeads"],
            form[role="search"] button[type="submit"],
            .search-bar button[type="submit"],
            form[method="GET"] button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]):not([class*="bg-gray"]):not([class*="text-slate"]),
            form[class*="filter"] button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]):not([class*="bg-gray"]):not([class*="text-slate"]),
            form.filter-bar button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]):not([class*="bg-gray"]):not([class*="text-slate"]),
            .filter-bar button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]):not([class*="bg-gray"]):not([class*="text-slate"]) {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border: 1px solid ${hex} !important;
                color: #ffffff !important;
                box-shadow: none !important;
            }

            .btn-search *,
            .btn-filter *,
            .btn-adjust *,
            form[method="GET"] button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]):not([class*="bg-gray"]) * {
                color: #ffffff !important;
            }

            .btn-search:hover,
            button.btn-search:hover,
            .btn-filter:hover,
            button.btn-filter:hover,
            a.btn-filter:hover,
            .btn-adjust:hover,
            button.btn-adjust:hover,
            button[onclick*="search"]:hover,
            button[onclick*="Search"]:hover,
            button[onclick*="filter"]:hover,
            button[onclick*="Filter"]:hover,
            form[role="search"] button[type="submit"]:hover,
            form[method="GET"] button[type="submit"]:not(.btn-clear):hover,
            form[class*="filter"] button[type="submit"]:not(.btn-clear):hover,
            form.filter-bar button[type="submit"]:not(.btn-clear):hover,
            .filter-bar button[type="submit"]:not(.btn-clear):hover {
                background: ${hoverHex} !important;
                background-color: ${hoverHex} !important;
                background-image: none !important;
                border-color: ${hoverHex} !important;
                color: #ffffff !important;
            }

            /* 6. GENERAL FORM SUBMIT BUTTONS (Create/Edit records) */
            form button[type="submit"]:not(.btn-cancel):not(.btn-clear):not(.btn-submit):not([class*="bg-rose"]):not([class*="bg-red"]):not([class*="bg-slate"]):not([class*="bg-gray"]):not([class*="text-rose"]):not([class*="bg-transparent"]):not(.btn-secondary) {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
            }
            form button[type="submit"]:not(.btn-cancel):not(.btn-clear):not(.btn-submit):not([class*="bg-rose"]):not([class*="bg-red"]):not([class*="bg-slate"]):not([class*="bg-gray"]):not([class*="text-rose"]):not([class*="bg-transparent"]):not(.btn-secondary):hover {
                background: ${hoverHex} !important;
                background-color: ${hoverHex} !important;
                background-image: none !important;
                border-color: ${hoverHex} !important;
            }

            /* 7. QUICK ACTION BUTTON */
            .crm-quick-action-btn,
            button.crm-quick-action-btn,
            button.bg-blue-50.text-blue-700,
            div[x-data*="open"] > button.bg-blue-50,
            div[x-data*="open"] > button.crm-quick-action-btn {
                background-color: rgba(${tr}, ${tg}, ${tb}, 0.12) !important;
                background-image: none !important;
                color: ${hex} !important;
                border: 1px solid rgba(${tr}, ${tg}, ${tb}, 0.3) !important;
            }
            .crm-quick-action-btn:hover,
            button.crm-quick-action-btn:hover,
            button.bg-blue-50.text-blue-700:hover,
            div[x-data*="open"] > button.crm-quick-action-btn:hover {
                background-color: ${hex} !important;
                background-image: none !important;
                color: #ffffff !important;
                border-color: ${hex} !important;
            }
            .crm-quick-action-btn svg,
            button.bg-blue-50.text-blue-700 svg {
                color: inherit !important;
            }

            /* 7b. CUSTOMER PORTAL DYNAMIC PILLS, ICON BOXES & BADGES */
            .crm-customer-pill,
            span.crm-customer-pill {
                background-color: rgba(${tr}, ${tg}, ${tb}, 0.12) !important;
                color: ${hex} !important;
                border: 1px solid rgba(${tr}, ${tg}, ${tb}, 0.28) !important;
            }
            .crm-customer-icon-box,
            div.crm-customer-icon-box {
                background-color: rgba(${tr}, ${tg}, ${tb}, 0.12) !important;
                color: ${hex} !important;
                border: 1px solid rgba(${tr}, ${tg}, ${tb}, 0.28) !important;
            }
            .crm-customer-badge,
            span.crm-customer-badge {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                color: #ffffff !important;
            }

            /* 8. ACTIVE TABS & VIEW PILLS */
            .filter-tab.active,
            html:not(.dark) .filter-tab.active,
            html.dark .filter-tab.active,
            .filter-tabs .filter-tab.active,
            .custom-view-pill.active,
            button[id^="view-tab-"].active,
            .nav-tab.active,
            .tab-active,
            .crm-tab-active,
            .crm-pill-active,
            button[role="tab"][aria-selected="true"] {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: none !important;
            }

            /* 9. TABLE VIEW DETAILS ACTION BUTTONS */
            .btn-view,
            a.btn-view,
            button.btn-view {
                background-color: rgba(${tr}, ${tg}, ${tb}, 0.1) !important;
                background-image: none !important;
                color: ${hex} !important;
                border: 1px solid rgba(${tr}, ${tg}, ${tb}, 0.25) !important;
            }
            .btn-view:hover,
            a.btn-view:hover,
            button.btn-view:hover {
                background-color: ${hex} !important;
                background-image: none !important;
                color: #ffffff !important;
                border-color: ${hex} !important;
            }

            /* 10. PAGINATION ACTIVE PAGE */
            nav[role="navigation"] span[aria-current="page"] > span,
            nav[role="navigation"] span[aria-current="page"] *,
            .pagination .active *,
            .pagination .page-item.active .page-link,
            span[aria-current="page"] > span {
                background: ${hex} !important;
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
            }

            /* 11. FORM INPUT FOCUS & CHECKBOXES */
            input[type="checkbox"]:checked,
            input[type="radio"]:checked {
                accent-color: ${hex} !important;
            }

            input:focus,
            select:focus,
            textarea:focus,
            input[type="search"]:focus,
            input[placeholder*="Search"]:focus {
                border-color: ${hex} !important;
                --tw-ring-color: ${hex} !important;
                outline-color: ${hex} !important;
            }

            /* 12. TEXT ACCENTS, LABELS & ALL MODULE CONTENT LINKS */
            .text-blue-600:not(.btn-primary):not([class*="bg-blue"]):not([class*="bg-[#2563eb]"]):not(.btn-search):not(.btn-filter):not(.text-white),
            .text-blue-500:not(.btn-primary):not([class*="bg-blue"]):not([class*="bg-[#2563eb]"]):not(.btn-search):not(.btn-filter):not(.text-white),
            .text-blue-700:not(.btn-primary):not([class*="bg-blue"]):not([class*="bg-[#2563eb]"]):not(.btn-search):not(.btn-filter):not(.text-white),
            .text-blue-800:not(.btn-primary):not([class*="bg-blue"]):not(.text-white),
            .text-indigo-600:not(.btn-primary):not([class*="bg-indigo"]):not(.text-white),
            .text-indigo-700:not(.btn-primary):not([class*="bg-indigo"]):not(.text-white),
            .text-indigo-800:not(.btn-primary):not([class*="bg-indigo"]):not(.text-white),
            .text-sky-600:not(.btn-primary):not([class*="bg-sky"]):not(.text-white),
            .text-sky-700:not(.btn-primary):not([class*="bg-sky"]):not(.text-white),
            .text-purple-600:not(.btn-primary):not([class*="bg-purple"]):not(.text-white),
            .text-purple-700:not(.btn-primary):not([class*="bg-purple"]):not(.text-white),
            .text-purple-800:not(.btn-primary):not([class*="bg-purple"]):not(.text-white),
            html.dark .dark\:text-blue-400:not(.btn-primary):not(.text-white),
            html.dark .dark\:text-blue-300:not(.btn-primary):not(.text-white),
            html.dark .dark\:text-indigo-400:not(.btn-primary):not(.text-white),
            html.dark .dark\:text-indigo-300:not(.btn-primary):not(.text-white),
            html.dark .dark\:text-sky-400:not(.btn-primary):not(.text-white),
            html.dark .dark\:text-sky-300:not(.btn-primary):not(.text-white),
            html.dark .dark\:text-purple-400:not(.btn-primary):not(.text-white),
            html.dark .dark\:text-purple-300:not(.btn-primary):not(.text-white),
            .cell-name a,
            .cell-no a,
            .leads-table .cell-name a,
            .ld-root .ld-scroll table.ld-table td a.ld-name,
            a.ld-name,
            table td a:not(.btn):not([class*="btn"]):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-slate"]):not([class*="text-gray"]):not(.text-white),
            a:not(.btn):not(.btn-primary):not(.btn-secondary):not(.btn-subtle):not(.btn-inline-add):not([class*="btn-"]):not(aside a):not(.crm-brand-logo):not(.text-white):not([class*="text-white"]):not(.crm-filter-tab):not(.filter-tab):not(.active):not([class*="active"]):not(.filter-tabs a):not(.section-nav-link):not([class*="text-emerald"]):not([class*="text-amber"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-slate"]):not([class*="text-gray"]:not([class*="underline"])),
            td[x-show*="map"] a,
            a[href*="projects/"]:not(.btn):not([class*="text-emerald"]):not([class*="text-rose"]),
            a[href*="workers/"]:not(.btn),
            a[href*="suppliers/"]:not(.btn),
            a[href*="leads/"]:not(.btn),
            a[href*="quotations/"]:not(.btn) {
                color: ${hex} !important;
            }
            .cell-name a:hover,
            .cell-no a:hover,
            .leads-table .cell-name a:hover,
            .ld-root .ld-scroll table.ld-table td a.ld-name:hover,
            a.ld-name:hover,
            table td a:hover,
            td[x-show*="map"] a:hover {
                color: ${hoverHex} !important;
            }

            /* 13. ACCENT BADGES, STATUS PILLS, DOTS & TIMELINE */
            span.rounded-full.bg-blue-600,
            span.rounded-full.bg-blue-500,
            span.rounded-full.bg-sky-500,
            span.rounded-full.bg-indigo-600,
            span.rounded-full.bg-indigo-500,
            span.w-3.h-3.rounded-full.bg-blue-600,
            span.w-2\.5.h-2\.5.rounded-full.bg-blue-500,
            span.w-2\.5.h-2\.5.rounded-full.bg-blue-600 {
                background-color: ${hex} !important;
            }

            .bg-blue-50:not(.btn-primary):not(.btn),
            .bg-blue-100:not(.btn-primary):not(.btn),
            .bg-indigo-50:not(.btn-primary):not(.btn),
            .bg-indigo-100:not(.btn-primary):not(.btn),
            .bg-purple-50:not(.btn-primary):not(.btn),
            .bg-purple-100:not(.btn-primary):not(.btn),
            .bg-sky-50:not(.btn-primary):not(.btn),
            .bg-sky-100:not(.btn-primary):not(.btn),
            html.dark .dark\:bg-blue-950\/60,
            html.dark .dark\:bg-blue-950\/70,
            html.dark .dark\:bg-blue-950\/30,
            html.dark .dark\:bg-indigo-900\/30,
            html.dark .dark\:bg-indigo-950\/30,
            html.dark .dark\:bg-indigo-950,
            html.dark .dark\:bg-purple-950\/30,
            html.dark .dark\:bg-purple-950\/40,
            html.dark .dark\:bg-sky-950\/40 {
                background-color: rgba(${tr}, ${tg}, ${tb}, 0.12) !important;
            }

            .border-blue-100,
            .border-blue-200,
            .border-blue-300,
            .border-indigo-100,
            .border-indigo-200,
            .border-purple-100,
            .border-purple-200,
            .border-sky-100,
            .border-sky-200,
            html.dark .dark\:border-blue-800,
            html.dark .dark\:border-blue-900\/40,
            html.dark .dark\:border-indigo-800,
            html.dark .dark\:border-purple-800,
            html.dark .dark\:border-sky-800 {
                border-color: rgba(${tr}, ${tg}, ${tb}, 0.3) !important;
            }

            .border-l-2.border-blue-500,
            .border-l-2.border-blue-600,
            .border-l-2.border-indigo-500,
            .border-b-2.border-blue-600,
            .border-b-2.border-indigo-600 {
                border-color: ${hex} !important;
            }

            /* Plain tabs (e.g. Calendar tabs) */
            .crm-plain-tab.active,
            html.dark .crm-plain-tab.active {
                color: ${hex} !important;
                border-bottom-color: ${hex} !important;
            }

            /* Active Timeframe & Tab Pills */
            .crm-tab-active,
            .crm-tab-active *,
            .crm-pill-active,
            .crm-pill-active *,
            a.crm-tab-active,
            a.crm-tab-active *,
            a.crm-pill-active,
            a.crm-pill-active * {
                background-color: ${hex} !important;
                color: #ffffff !important;
            }

            /* Search icon dimensional guard */
            .relative.flex.items-center > svg.pointer-events-none {
                width: 16px !important;
                height: 16px !important;
                min-width: 16px !important;
                max-width: 16px !important;
                min-height: 16px !important;
                max-height: 16px !important;
            }
        `;

        // Automatically recolor all logo images on the page
        if (typeof window.recolorCrmBrandLogo === 'function') {
            window.recolorCrmBrandLogo(hex);

        // Dynamically recolor any Chart.js bar and line charts live on the page
        try {
            if (window.Chart && window.Chart.instances) {
                Object.values(window.Chart.instances).forEach(function(chart) {
                    if (chart && chart.data && chart.data.datasets) {
                        chart.data.datasets.forEach(function(ds) {
                            if (ds.label === 'Accepted Revenue' || ds.type === 'bar' || (!ds.type && chart.config.type === 'bar')) {
                                ds.backgroundColor = hex;
                            }
                        });
                        chart.update('none');
                    }
                });
            }
        } catch(e) {}
        }
    };

    // Cross-page mode lookup: checks user-scoped key, generic key, legacy 'theme' key, and cookie
    function getCrmCookie(name) {
        try {
            const v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
            return v ? decodeURIComponent(v[2]) : null;
        } catch(e) { return null; }
    }
    const cookieMode = getCrmCookie('crm_mode');

    // Immediate execution on <head> parse (prevents FOUC)
    let storedMode = null;
    let storedStyle = null;
    let initialAccent = null;
    try {
        storedMode = localStorage.getItem(modeKey) || 
                     localStorage.getItem('crm_mode') || 
                     localStorage.getItem('theme') || 
                     cookieMode;
        storedStyle = localStorage.getItem(styleKey) || localStorage.getItem('crm_theme_style');
        initialAccent = localStorage.getItem(accentKey) || localStorage.getItem('crm_accent');
    } catch(e) {}

    let activeMode = storedMode || dbMode || 'night';
    if (activeMode === 'light') activeMode = 'day';
    if (activeMode === 'dark') activeMode = 'night';

    let isDark = (activeMode === 'night');
    if (activeMode === 'auto') {
        isDark = Boolean(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    }

    if (isDark) {
        document.documentElement.classList.add('dark'); document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark'); document.documentElement.setAttribute('data-theme', 'light');
    }

    // Definitive Universal CRM Mode Switcher & Synchronizer
    window.applyCrmMode = function(mode, persistBackend) {
        if (persistBackend === undefined) persistBackend = true;
        let target = mode;
        if (target === 'light') target = 'day';
        if (target === 'dark') target = 'night';

        const isNight = (target === 'night') || 
                        (target === 'auto' && Boolean(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));

        if (isNight) {
            document.documentElement.classList.add('dark'); document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark'); document.documentElement.setAttribute('data-theme', 'light');
        }

        const standardMode = isNight ? 'night' : 'day';

        // Write to ALL keys for absolute consistency across any script/page
        try {
            localStorage.setItem('crm_mode', standardMode);
            localStorage.setItem('theme', isNight ? 'dark' : 'light');
            if (modeKey) localStorage.setItem(modeKey, standardMode);
            if (currentUserId) localStorage.setItem('crm_mode_user_' + currentUserId, standardMode);
            const expDate = new Date(Date.now() + 365*24*60*60*1000).toUTCString();
            document.cookie = "crm_mode=" + standardMode + "; path=/; expires=" + expDate + "; max-age=31536000; SameSite=Lax";
            document.cookie = "crm_mode=" + standardMode + "; path=/; expires=" + expDate;
        } catch(e) {}

        // Fire both custom events so all components (header, body, modals) update instantly
        window.dispatchEvent(new CustomEvent('theme-mode-changed', { detail: { mode: standardMode, isDark: isNight } }));
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { userId: currentUserId, mode: standardMode, isDark: isNight } }));

        // Persist to backend database and session with keepalive so page navigation cannot abort it
        if (persistBackend) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                try {
                    fetch('/profile/theme', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ mode: standardMode }),
                        keepalive: true
                    }).catch(function() {});
                } catch(e) {}
            }
        }

        return isNight;
    };

    window.crmToggleTheme = function() {
        const isCurrentlyDark = document.documentElement.classList.contains('dark');
        return window.applyCrmMode(isCurrentlyDark ? 'day' : 'night', true);
    };

    const themeStyle = storedStyle || dbStyle || 'lite';
    document.documentElement.setAttribute('data-header-theme', themeStyle);

    const activeAccent = initialAccent || dbAccent || '#2563eb';
    window.applyGlobalCrmAccent(activeAccent);

    // Re-apply once DOM is ready so late-rendered elements get recolored logo
    document.addEventListener('DOMContentLoaded', function() {
        let accent = null;
        try { accent = localStorage.getItem(accentKey); } catch(e) {}
        if (!accent) accent = dbAccent || '#2563eb';
        window.applyGlobalCrmAccent(accent);
        window.recolorCrmBrandLogo(accent);
        const styleEl = document.getElementById('crm-dynamic-theme-overrides');
        if (styleEl && document.body && (styleEl.parentNode !== document.body || styleEl.nextSibling !== null)) {
            document.body.appendChild(styleEl);
        }
    });

    // Cross-tab and custom event synchronization - strictly scoped to this user
    window.addEventListener('theme-changed', function(e) {
        if (e.detail && (!e.detail.userId || e.detail.userId == currentUserId)) {
            if (e.detail.accent) {
                window.applyGlobalCrmAccent(e.detail.accent);
                window.recolorCrmBrandLogo(e.detail.accent);
            }
        }
    });

    window.addEventListener('storage', function(e) {
        if (e.key === accentKey && e.newValue) {
            window.applyGlobalCrmAccent(e.newValue);
            window.recolorCrmBrandLogo(e.newValue);
        }
        if ((e.key === modeKey || e.key === 'crm_mode' || e.key === 'theme') && e.newValue) {
            window.applyCrmMode(e.newValue, false);
        }
    });
})();
</script>
