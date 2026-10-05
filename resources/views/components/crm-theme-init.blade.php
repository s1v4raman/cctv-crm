{{-- Universal Realtime CRM Theme & Dynamic Logo Recoloring Engine --}}
<script>
(function() {
    window.crmLogoCache = window.crmLogoCache || {};

    // Dynamic High-Quality Canvas Logo Recoloring Function
    window.recolorCrmBrandLogo = function(hex) {
        if (!hex) hex = localStorage.getItem('crm_accent') || '#be123c';

        function applyLogoSrc(dataUrl) {
            if (!dataUrl) return;
            const logoSelectors = 'img.crm-brand-logo, img[src*="logo.png"], img[alt*="Precision IT Systems"], img[alt*="Precision IT Systems Logo"]';
            document.querySelectorAll(logoSelectors).forEach(function(img) {
                if (img.src !== dataUrl) {
                    img.src = dataUrl;
                }
            });
            const favicon = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
            if (favicon) favicon.href = dataUrl;
        }

        // Return from memory cache if available
        if (window.crmLogoCache[hex]) {
            applyLogoSrc(window.crmLogoCache[hex]);
            return;
        }

        // Return from persistent localStorage cache if available (0ms instant render)
        const cachedKey = 'crm_recolored_logo_' + hex.replace('#', '').toLowerCase();
        try {
            const cachedData = localStorage.getItem(cachedKey);
            if (cachedData) {
                window.crmLogoCache[hex] = cachedData;
                applyLogoSrc(cachedData);
                return;
            }
        } catch(e) {}

        // Preload base logo and recolor synchronously if complete
        window.crmBaseLogoImg = window.crmBaseLogoImg || new Image();
        if (!window.crmBaseLogoImg.src) {
            window.crmBaseLogoImg.src = window.location.origin + '/logo.png';
        }

        function processImg(img) {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 256;
                canvas.height = 256;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, 256, 256);

                const imgData = ctx.getImageData(0, 0, 256, 256);
                const d = imgData.data;

                // Parse target hex color
                let cleanHex = hex.replace('#', '');
                if (cleanHex.length === 3) cleanHex = cleanHex.split('').map(function(c) { return c + c; }).join('');
                const num = parseInt(cleanHex, 16);
                const tr = (num >> 16) & 255;
                const tg = (num >> 8) & 255;
                const tb = num & 255;

                for (let i = 0; i < d.length; i += 4) {
                    const a = d[i + 3];
                    if (a < 15) continue; // transparent background

                    const r = d[i];
                    const g = d[i + 1];
                    const b = d[i + 2];

                    // Preserve pure white & near-white highlights (banner, stars, white details)
                    if (r > 200 && g > 200 && b > 200 && Math.abs(r - g) < 30 && Math.abs(g - b) < 30) {
                        continue;
                    }

                    // Calculate stroke intensity for smooth antialiased edges
                    const brightness = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                    const inkStrength = Math.max(0, Math.min(1, 1 - brightness));

                    d[i]     = Math.min(255, Math.max(0, Math.round(255 - inkStrength * (255 - tr))));
                    d[i + 1] = Math.min(255, Math.max(0, Math.round(255 - inkStrength * (255 - tg))));
                    d[i + 2] = Math.min(255, Math.max(0, Math.round(255 - inkStrength * (255 - tb))));
                }

                ctx.putImageData(imgData, 0, 0);
                const coloredUrl = canvas.toDataURL('image/png');
                window.crmLogoCache[hex] = coloredUrl;
                try {
                    localStorage.setItem(cachedKey, coloredUrl);
                } catch(e) {}
                applyLogoSrc(coloredUrl);
            } catch(e) {
                console.warn('Canvas logo recoloring note:', e);
            }
        }

        if (window.crmBaseLogoImg.complete && window.crmBaseLogoImg.naturalWidth > 0) {
            processImg(window.crmBaseLogoImg);
        } else {
            window.crmBaseLogoImg.addEventListener('load', function() {
                processImg(window.crmBaseLogoImg);
            }, { once: true });
        }
    };

    // DOM MutationObserver to catch dynamically injected or swapped logo images
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function() {
            const hex = localStorage.getItem('crm_accent') || '#be123c';
            const dataUrl = window.crmLogoCache[hex] || localStorage.getItem('crm_recolored_logo_' + hex.replace('#', '').toLowerCase());
            if (dataUrl) {
                const logoSelectors = 'img.crm-brand-logo, img[src*="logo.png"], img[alt*="Precision IT Systems"], img[alt*="Precision IT Systems Logo"]';
                document.querySelectorAll(logoSelectors).forEach(function(img) {
                    if (img.src !== dataUrl) {
                        img.src = dataUrl;
                    }
                });
            }
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
        if (!hex) hex = '#be123c';
        
        let hoverHex = hex;
        let tr = 190, tg = 18, tb = 60;
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
        document.head.appendChild(styleEl);

        styleEl.textContent = `
            :root {
                --crm-accent: ${hex} !important;
                --crm-accent-hover: ${hoverHex} !important;
                --crm-accent-shadow: ${hex}40 !important;
                --crm-accent-rgb: ${tr}, ${tg}, ${tb} !important;
                --brand-blue: ${hex} !important;
                --brand-blue-hover: ${hoverHex} !important;
            }
            
            /* Logo wordmark accent text: Precision IT [Systems] */
            .crm-brand-accent-text,
            .crm-brand-accent-text * {
                color: ${hex} !important;
            }

            /* Initial avatar badge gradient (matches exact chosen accent) */
            .crm-user-avatar-badge,
            button[aria-label="User account menu"] {
                background: linear-gradient(135deg, ${hex} 0%, ${hoverHex} 100%) !important;
                box-shadow: 0 4px 14px -1px ${hex}55 !important;
            }

            /* 1. SIDEBAR & NAVIGATION ACTIVE / ACCENT BUTTONS */
            aside nav a.crm-active-link,
            aside nav a[class*="!bg-blue-600"],
            aside nav a[class*="bg-blue-600"],
            .crm-active-link,
            [class*="!bg-blue-600"] {
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px -1px ${hex}55 !important;
            }

            aside nav a.crm-active-link:hover,
            aside nav a[class*="!bg-blue-600"]:hover,
            .crm-active-link:hover {
                background-color: ${hoverHex} !important;
                border-color: ${hoverHex} !important;
            }

            /* Sidebar User Accounts & Settings navigation button */
            aside nav a[href*="admin/users"].crm-active-link,
            aside nav a[href*="admin/users"][class*="!bg-blue-600"],
            aside nav a[href*="admin.users"].crm-active-link {
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px -1px ${hex}55 !important;
            }
            aside nav a[href*="admin/users"]:hover:not(.crm-active-link) svg,
            aside nav a[href*="admin.users"]:hover:not(.crm-active-link) svg {
                color: ${hex} !important;
            }

            /* 2. ALL MODULE PRIMARY ACTION BUTTONS (+ New, Create, Save, Submit, Export, Book) */
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
            .crm-hub-subnav-pill,
            a.crm-hub-subnav-pill,
            a[class*="from-amber-500"],
            a[class*="to-amber-600"],
            a[class*="bg-amber-500"]:not([href*="range="]),
            a[class*="from-emerald-500"],
            a[class*="to-emerald-600"],
            a[class*="bg-emerald-600"]:not([href*="range="]),
            button[class*="from-amber-500"],
            button[class*="to-amber-600"],
            button[class*="bg-amber-500"],
            button[class*="from-emerald-500"],
            button[class*="to-emerald-600"],
            a[href*="export-pdf"],
            a[href*="rma/create"],
            a[href*="rma.create"],
            a[href*="leads/create"],
            a[href*="quotations/create"],
            a[href*="service-tickets/create"],
            a[href*="purchase-orders/create"],
            a[href*="invoices/create"],
            a[href*="products/create"],
            a[href*="portal/tickets/create"],
            a[href*="portal.tickets.create"],
            a[href*="payment/checkout/quotation"],
            button[onclick*="siteSurveyBookingModal"],
            button[onclick*="amcRenewalModal"],
            .modal-confirm-btn,
            button.btn-confirm,
            form button[type="submit"]:not(.btn-cancel):not(.btn-clear):not([class*="bg-rose"]):not([class*="bg-red"]):not([class*="bg-slate"]):not([class*="bg-gray"]):not([class*="text-rose"]):not([class*="bg-transparent"]) {
                background: linear-gradient(135deg, ${hex}, ${hoverHex}) !important;
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px -1px ${hex}55 !important;
            }

            button.bg-blue-600:hover,
            button[class*="bg-blue-600"]:hover,
            a.bg-blue-600:hover,
            a[class*="bg-blue-600"]:hover,
            button[class*="bg-[#2563eb]"]:hover,
            a[class*="bg-[#2563eb]"]:hover,
            button[class*="bg-blue-700"]:hover,
            a[class*="bg-blue-700"]:hover,
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
            button.crm-customer-action-btn:hover,
            a.crm-customer-action-btn:hover,
            .crm-customer-header-action:hover,
            a.crm-customer-header-action:hover,
            .crm-hub-primary-btn:hover,
            button.crm-hub-primary-btn:hover,
            a.crm-hub-primary-btn:hover,
            .crm-hub-subnav-pill:hover,
            a.crm-hub-subnav-pill:hover,
            a[class*="from-amber-500"]:hover,
            a[class*="to-amber-600"]:hover,
            a[class*="bg-amber-500"]:not([href*="range="]):hover,
            a[class*="from-emerald-500"]:hover,
            a[class*="to-emerald-600"]:hover,
            a[class*="bg-emerald-600"]:not([href*="range="]):hover,
            button[class*="from-amber-500"]:hover,
            button[class*="to-amber-600"]:hover,
            button[class*="bg-amber-500"]:hover,
            button[class*="from-emerald-500"]:hover,
            button[class*="to-emerald-600"]:hover,
            a[href*="export-pdf"]:hover,
            a[href*="rma/create"]:hover,
            a[href*="leads/create"]:hover,
            a[href*="quotations/create"]:hover,
            a[href*="service-tickets/create"]:hover,
            a[href*="purchase-orders/create"]:hover,
            a[href*="invoices/create"]:hover,
            a[href*="products/create"]:hover,
            a[href*="portal/tickets/create"]:hover,
            a[href*="portal.tickets.create"]:hover,
            a[href*="payment/checkout/quotation"]:hover,
            button[onclick*="siteSurveyBookingModal"]:hover,
            button[onclick*="amcRenewalModal"]:hover,
            .modal-confirm-btn:hover,
            button.btn-confirm:hover,
            form button[type="submit"]:not(.btn-cancel):not(.btn-clear):not([class*="bg-rose"]):not([class*="bg-red"]):not([class*="bg-slate"]):not([class*="bg-gray"]):hover {
                background: linear-gradient(135deg, ${hoverHex}, ${hex}) !important;
                background-color: ${hoverHex} !important;
                border-color: ${hoverHex} !important;
                color: #ffffff !important;
                box-shadow: 0 6px 18px -1px ${hex}66 !important;
            }

            /* Hub Secondary Module Navigation Buttons */
            .crm-hub-action-btn,
            a.crm-hub-action-btn,
            button.crm-hub-action-btn {
                background-color: ${hex}16 !important;
                border-color: ${hex}38 !important;
                color: ${hex} !important;
            }
            .crm-hub-action-btn svg,
            a.crm-hub-action-btn svg {
                color: ${hex} !important;
            }
            .crm-hub-action-btn:hover,
            a.crm-hub-action-btn:hover,
            button.crm-hub-action-btn:hover {
                background: linear-gradient(135deg, ${hex}, ${hoverHex}) !important;
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 4px 12px -1px ${hex}55 !important;
            }
            .crm-hub-action-btn:hover svg,
            a.crm-hub-action-btn:hover svg,
            .crm-hub-action-btn:hover span,
            a.crm-hub-action-btn:hover span {
                color: #ffffff !important;
            }

            /* 3. FILTER BUTTONS & FILTER CONTROLS */
            .btn-filter,
            button.btn-filter,
            a.btn-filter,
            .btn-adjust,
            button.btn-adjust,
            a.btn-adjust,
            button[onclick*="filter"],
            button[onclick*="Filter"],
            button[onclick*="filterLeads"],
            form[class*="filter"] button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]),
            form.filter-bar button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]),
            .filter-bar button[type="submit"]:not(.btn-clear):not([class*="bg-slate"]):not([class*="bg-rose"]) {
                background: linear-gradient(135deg, ${hex}, ${hoverHex}) !important;
                background-color: ${hex} !important;
                background-image: none !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 2px 8px -1px ${hex}55 !important;
            }

            .btn-filter:hover,
            button.btn-filter:hover,
            a.btn-filter:hover,
            .btn-adjust:hover,
            button.btn-adjust:hover,
            a.btn-adjust:hover,
            button[onclick*="filter"]:hover,
            button[onclick*="Filter"]:hover,
            button[onclick*="filterLeads"]:hover,
            form[class*="filter"] button[type="submit"]:not(.btn-clear):hover,
            form.filter-bar button[type="submit"]:not(.btn-clear):hover,
            .filter-bar button[type="submit"]:not(.btn-clear):hover {
                background-color: ${hoverHex} !important;
                border-color: ${hoverHex} !important;
                color: #ffffff !important;
            }

            /* Active Filter Tabs & Pills */
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
            div.flex > a[href*="analytics"][class*="from-amber-500"],
            div.flex > a[href*="analytics"][class*="bg-amber-500"],
            div.flex > a[href*="analytics"][class*="from-emerald-500"],
            div.flex > a[href*="analytics"][class*="bg-emerald-500"],
            div.flex > a[href*="analytics"][class*="bg-blue-600"],
            a[href*="analytics"][href*="range="][class*="bg-amber-500"],
            a[href*="analytics"][href*="range="][class*="bg-blue-600"],
            a[href*="analytics"][href*="range="][class*="bg-emerald-600"] {
                background: linear-gradient(135deg, ${hex}, ${hoverHex}) !important;
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px -1px ${hex}55 !important;
            }

            /* 4. SEARCH BUTTONS & SEARCH INPUT FOCUS */
            .btn-search,
            button.btn-search,
            button[onclick*="search"],
            button[onclick*="Search"],
            form[role="search"] button[type="submit"],
            .search-bar button[type="submit"] {
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
            }
            .btn-search:hover,
            button.btn-search:hover,
            form[role="search"] button[type="submit"]:hover {
                background-color: ${hoverHex} !important;
                border-color: ${hoverHex} !important;
            }

            input[type="search"]:focus,
            input[placeholder*="Search"]:focus,
            input[placeholder*="search"]:focus {
                border-color: ${hex} !important;
                --tw-ring-color: ${hex} !important;
                outline-color: ${hex} !important;
            }

            /* 5. QUICK ACTION & DROPDOWN TRIGGER BUTTONS */
            button.bg-blue-50.text-blue-700,
            div[x-data*="open"] > button.bg-blue-50,
            div[x-data*="open"] > button[class*="text-blue-700"] {
                background-color: ${hex}18 !important;
                color: ${hex} !important;
                border-color: ${hex}40 !important;
            }
            button.bg-blue-50.text-blue-700:hover,
            div[x-data*="open"] > button.bg-blue-50:hover {
                background-color: ${hex}28 !important;
                border-color: ${hex}60 !important;
            }
            button.bg-blue-50.text-blue-700 svg,
            div[x-data*="open"] > button.bg-blue-50 svg {
                color: ${hex} !important;
            }

            /* 6. PAGINATION ACTIVE PAGE BUTTON */
            nav[role="navigation"] span[aria-current="page"] > span,
            nav[role="navigation"] span[aria-current="page"] *,
            .pagination .active *,
            .pagination .page-item.active .page-link,
            span[aria-current="page"] > span {
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
                box-shadow: 0 2px 8px ${hex}55 !important;
            }

            /* 7. CHECKBOXES, RADIOS & ACTIVE CONTROLS */
            input[type="checkbox"]:checked,
            input[type="radio"]:checked {
                accent-color: ${hex} !important;
            }

            /* 8. ACTIVE TEXT, ICONS & SUBTLE BADGES */
            .text-blue-600,
            .text-blue-500,
            .text-blue-700,
            .dark .dark\\:text-blue-400,
            .dark .dark\\:text-blue-300 {
                color: ${hex} !important;
            }

            .bg-blue-50 {
                background-color: ${hex}18 !important;
            }
            .dark .dark\\:bg-blue-950\\/60,
            .dark .dark\\:bg-blue-950 {
                background-color: ${hex}28 !important;
            }

            .border-blue-200,
            .dark .dark\\:border-blue-800 {
                border-color: ${hex}40 !important;
            }

            /* Focus rings */
            .focus\\:ring-blue-600:focus,
            .focus\\:ring-blue-500:focus,
            button:focus-visible,
            a:focus-visible {
                --tw-ring-color: ${hex} !important;
                outline-color: ${hex} !important;
            }

            /* Brand Logo Tile & User Avatars */
            .crm-brand-tile,
            .crm-user-avatar {
                background: linear-gradient(135deg, ${hex}, ${hoverHex}) !important;
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
    const storedMode = localStorage.getItem('crm_mode') || localStorage.getItem('theme') || 'auto';
    const isDark = (storedMode === 'night' || storedMode === 'dark') || 
                   (storedMode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    const themeStyle = localStorage.getItem('crm_theme_style') || 'dark';
    document.documentElement.setAttribute('data-header-theme', themeStyle);

    const initialAccent = localStorage.getItem('crm_accent') || '#be123c';
    window.applyGlobalCrmAccent(initialAccent);

    // Re-apply once DOM is ready so late-rendered elements get recolored logo
    document.addEventListener('DOMContentLoaded', function() {
        const accent = localStorage.getItem('crm_accent') || '#be123c';
        window.applyGlobalCrmAccent(accent);
        window.recolorCrmBrandLogo(accent);
    });

    // Cross-tab and custom event synchronization
    window.addEventListener('theme-changed', function(e) {
        if (e.detail && e.detail.accent) {
            window.applyGlobalCrmAccent(e.detail.accent);
            window.recolorCrmBrandLogo(e.detail.accent);
        }
    });

    window.addEventListener('storage', function(e) {
        if (e.key === 'crm_accent' && e.newValue) {
            window.applyGlobalCrmAccent(e.newValue);
            window.recolorCrmBrandLogo(e.newValue);
        }
    });
})();
</script>
