@props([
    'userId' => null,
    'userAccent' => null,
    'userMode' => null,
    'userStyle' => null,
])
{{-- Universal Realtime CRM Theme & Dynamic Logo Recoloring Engine --}}
<script>
(function() {
    window.crmLogoCache = window.crmLogoCache || {};

    const currentUserId = {{ json_encode($userId ?? (auth()->check() ? auth()->id() : null)) }};
    const dbAccent = {{ json_encode($userAccent ?? (auth()->check() ? auth()->user()->theme_accent : null)) }} || '#2563eb';
    const dbMode = {{ json_encode($userMode ?? (auth()->check() ? auth()->user()->theme_mode : null)) }} || 'auto';
    const dbStyle = {{ json_encode($userStyle ?? (auth()->check() ? auth()->user()->theme_style : null)) }} || 'dark';

    // Scoped storage keys per user to guarantee absolute isolation between users
    const accentKey = currentUserId ? ('crm_accent_user_' + currentUserId) : 'crm_accent';
    const modeKey = currentUserId ? ('crm_mode_user_' + currentUserId) : 'crm_mode';
    const styleKey = currentUserId ? ('crm_theme_style_user_' + currentUserId) : 'crm_theme_style';

    window.crmCurrentUserId = currentUserId;
    window.crmAccentStorageKey = accentKey;
    window.crmModeStorageKey = modeKey;
    window.crmStyleStorageKey = styleKey;
    window.crmUserDefaultAccent = dbAccent;

    // Dynamic Instant High-Clarity Logo Color Switcher Function
    window.recolorCrmBrandLogo = function(hex) {
        if (!hex) {
            try { hex = localStorage.getItem(accentKey); } catch(e) {}
            if (!hex) hex = dbAccent || '#2563eb';
        }
        let cleanHex = hex.replace('#', '').toLowerCase();
        if (cleanHex.length === 3) cleanHex = cleanHex.split('').map(function(c) { return c + c; }).join('');
        const logoUrl = '/logos/logo_' + cleanHex + '.png';

        function applyLogoSrc() {
            // Guard: Never change logo on customer storefront, customer login, or staff login pages
            const path = window.location.pathname;
            if (path === '/' || path === '/login' || path === '/staff/login') {
                return;
            }

            const logoSelectors = 'img.crm-brand-logo, aside img[src*="logo"], aside img[alt*="Precision IT Systems"]';
            document.querySelectorAll(logoSelectors).forEach(function(img) {
                if (img.getAttribute('src') !== logoUrl) {
                    img.src = logoUrl;
                }
            });
            const favicon = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
            if (favicon) favicon.href = logoUrl;
        }

        applyLogoSrc();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', applyLogoSrc);
        }
    };

    // DOM MutationObserver to catch late-rendered or swapped logo images
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function() {
            const path = window.location.pathname;
            if (path === '/' || path === '/login' || path === '/staff/login') {
                return;
            }
            let hex = null;
            try { hex = localStorage.getItem(accentKey); } catch(e) {}
            if (!hex) hex = dbAccent || '#2563eb';
            let cleanHex = hex.replace('#', '').toLowerCase();
            if (cleanHex.length === 3) cleanHex = cleanHex.split('').map(function(c) { return c + c; }).join('');
            const logoUrl = '/logos/logo_' + cleanHex + '.png';

            const logoSelectors = 'img.crm-brand-logo, aside img[src*="logo"], aside img[alt*="Precision IT Systems"]';
            document.querySelectorAll(logoSelectors).forEach(function(img) {
                if (img.getAttribute('src') !== logoUrl) {
                    img.src = logoUrl;
                }
            });
        });
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                observer.observe(document.body, { childList: true, subtree: true });
            });
        }
    }

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
                --brand-blue: ${hex} !important;
                --brand-blue-hover: ${hoverHex} !important;
            }
            
            /* 0. STRICT ELIMINATION OF ALL 2-COLOR GRADIENTS (Flat solid one color) */
            [style*="linear-gradient"],
            .saas-card [style*="linear-gradient"],
            div[style*="linear-gradient"],
            header[style*="linear-gradient"],
            button[style*="linear-gradient"],
            a[style*="linear-gradient"],
            span[style*="linear-gradient"] {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
            }
            button[style*="linear-gradient"]:hover,
            a[style*="linear-gradient"]:hover {
                background: ${hoverHex} !important;
                background-color: ${hoverHex} !important;
                background-image: none !important;
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
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            }

            /* 3. SIDEBAR & NAVIGATION ACTIVE LINKS */
            aside nav a.crm-active-link,
            aside nav a[class*="!bg-blue-600"],
            aside nav a[class*="bg-blue-600"],
            .crm-active-link,
            [class*="!bg-blue-600"] {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            }

            aside nav a.crm-active-link:hover,
            aside nav a[class*="!bg-blue-600"]:hover,
            .crm-active-link:hover {
                background: ${hoverHex} !important;
                background-color: ${hoverHex} !important;
                background-image: none !important;
                border-color: ${hoverHex} !important;
            }

            aside nav a:hover:not(.crm-active-link) svg {
                color: ${hex} !important;
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
            a[href*="export-pdf"],
            a[href*="/create"]:not(.btn-secondary):not([class*="bg-white"]):not([class*="bg-slate"]),
            a[href*=".create"]:not(.btn-secondary):not([class*="bg-white"]):not([class*="bg-slate"]),
            a[href*="payment/checkout"],
            button[onclick*="siteSurveyBookingModal"],
            button[onclick*="amcRenewalModal"] {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border: 1px solid ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            }

            .btn-primary *,
            .crm-btn-primary *,
            .btn-amber *,
            button.bg-blue-600 *,
            a.bg-blue-600 *,
            button[class*="bg-blue-600"] * {
                color: #ffffff !important;
                fill: currentColor;
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
            a[href*="export-pdf"]:hover,
            a[href*="/create"]:hover,
            a[href*=".create"]:hover,
            a[href*="payment/checkout"]:hover {
                background: ${hoverHex} !important;
                background-color: ${hoverHex} !important;
                background-image: none !important;
                border-color: ${hoverHex} !important;
                color: #ffffff !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
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
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
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
            form button[type="submit"]:not(.btn-cancel):not(.btn-clear):not(.btn-submit):not([class*="bg-rose"]):not([class*="bg-red"]):not([class*="bg-slate"]):not([class*="bg-gray"]):not([class*="text-rose"]):not([class*="bg-transparent"]) {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
            }
            form button[type="submit"]:not(.btn-cancel):not(.btn-clear):not(.btn-submit):not([class*="bg-rose"]):not([class*="bg-red"]):not([class*="bg-slate"]):not([class*="bg-gray"]):not([class*="text-rose"]):not([class*="bg-transparent"]):hover {
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
            button[role="tab"][aria-selected="true"],
            div.flex > a[href*="analytics"][class*="bg-"],
            a[href*="analytics"][href*="range="][class*="bg-"] {
                background: ${hex} !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
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

            /* 12. TEXT ACCENTS */
            .text-blue-600,
            .text-blue-500,
            .text-blue-700,
            .cell-name a,
            .cell-no a,
            .leads-table .cell-name a,
            .dark .dark\\:text-blue-400,
            .dark .dark\\:text-blue-300 {
                color: ${hex} !important;
            }
            .cell-name a:hover,
            .cell-no a:hover,
            .leads-table .cell-name a:hover {
                color: ${hoverHex} !important;
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
        }
    };

    // Immediate execution on <head> parse (prevents FOUC)
    let storedMode = null;
    let storedStyle = null;
    let initialAccent = null;
    try {
        storedMode = localStorage.getItem(modeKey);
        storedStyle = localStorage.getItem(styleKey);
        initialAccent = localStorage.getItem(accentKey);
    } catch(e) {}

    const activeMode = storedMode || dbMode || 'auto';
    const isDark = (activeMode === 'night' || activeMode === 'dark') || 
                   (activeMode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    const themeStyle = storedStyle || dbStyle || 'dark';
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
    });
})();
</script>
