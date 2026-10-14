@php
    $cookieMode = request()->cookie('crm_mode');
    $effectiveMode = (auth()->check() && auth()->user()->theme_mode === 'night') ? 'night' : 'day';
    $isDarkServer = false;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f8fafc]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Precision IT Systems CRM') }} | Smart Operations</title>

        <!-- Dynamic Logo Engine -->
        <x-crm-theme-init :userId="auth()->id()" :userAccent="auth()->user()->theme_accent" :userMode="auth()->user()->theme_mode" :userStyle="auth()->user()->theme_style" />

        <!-- Tailwind CSS & Alpine.js CDN Guard -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Arial', 'sans-serif'],
                            heading: ['Arial', 'sans-serif'],
                            mono: ['Arial', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                blue: '#2563eb',
                                blueHover: '#1d4ed8',
                                lightBg: '#f5f5f5',
                            }
                        }
                    }
                }
            }
        </script>

        <!-- Scripts & Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }
            html.dark {
                color-scheme: dark;
            }

            .custom-sidebar-scroll {
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #94a3b8 transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar {
                width: 5px;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-track {
                background: transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-thumb {
                background-color: #cbd5e1;
                border-radius: 9999px;
            }
            .dark .custom-sidebar-scroll::-webkit-scrollbar-thumb {
                background-color: #334155;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
                background-color: #94a3b8;
            }

            :root {
                --crm-accent: #2563eb;
                --crm-accent-hover: #1d4ed8;
            }

            /* Modern Stat Card Surface */
            .stat-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1rem 1.25rem;
                transition: all 0.2s ease-in-out;
            }
            html.dark .stat-card {
                background: #0f172a;
                border-color: #1e293b;
            }

            /* Header Bar: Clean standard background */
            header.crm-topbar,
            .crm-topbar {
                background-color: #ffffff !important;
                border-bottom: 1px solid #e2e8f0 !important;
            }
            html.dark header.crm-topbar,
            html.dark .crm-topbar {
                background-color: #0f172a !important;
                border-bottom: 1px solid #1e293b !important;
            }

            /* Dashboard & Page Name Font in Header */
            .crm-topbar .crm-page-title,
            .crm-topbar span.font-bold {
                font-size: 15px !important;
                font-weight: 700 !important;
                color: #0f172a !important;
                letter-spacing: -0.01em !important;
            }
            html.dark .crm-topbar .crm-page-title,
            html.dark .crm-topbar span.font-bold {
                color: #f8fafc !important;
            }

            /* Mobile menu & search toggle icons */
            .crm-topbar button[aria-label="Toggle navigation menu"],
            .crm-topbar button[title="Search"] {
                color: #64748b !important;
            }
            html.dark .crm-topbar button[aria-label="Toggle navigation menu"],
            html.dark .crm-topbar button[title="Search"] {
                color: #94a3b8 !important;
            }

            /* Crisp white background for search bar, theme toggle, notification, and profile */
            .crm-topbar input[type="text"],
            .crm-topbar .crm-search-input {
                background-color: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                color: #0f172a !important;
            }
            html.dark .crm-topbar input[type="text"],
            html.dark .crm-topbar .crm-search-input {
                background-color: #0b132b !important;
                border-color: #334155 !important;
                color: #ffffff !important;
            }

            .crm-topbar #header-theme-toggle-btn {
                background-color: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                color: #1e293b !important;
            }
            html.dark .crm-topbar #header-theme-toggle-btn {
                background-color: #1e293b !important;
                border-color: #334155 !important;
                color: #e2e8f0 !important;
            }

            .crm-topbar #header-notification-btn {
                background-color: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                color: #1e293b !important;
            }
            html.dark .crm-topbar #header-notification-btn {
                background-color: #1e293b !important;
                border-color: #334155 !important;
                color: #e2e8f0 !important;
            }

            .crm-topbar #header-user-profile-pill {
                background-color: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
            }
            html.dark .crm-topbar #header-user-profile-pill {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }

            /* Sidebar: Medium Matching Color Layout (#dbe6f6 / #cee0f4) */
            .crm-sidebar-container,
            aside {
                background-color: #dbe6f6 !important;
            }
            .crm-sidebar-header {
                background-color: #cee0f4 !important;
                border-bottom: 1px solid #b9d1ef !important;
            }
            .crm-sidebar-footer {
                background-color: #cee0f4 !important;
                border-top: 1px solid #b9d1ef !important;
            }

            /* Sidebar Module Navigation Click & Active State: Cohesive #2563eb */
            aside nav a.crm-active-link,
            .crm-active-link {
                background-color: #2563eb !important;
                border-radius: 8px !important;
                color: #ffffff !important;
                font-weight: 600 !important;
                box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
            }
            aside nav a.crm-active-link svg,
            .crm-active-link svg {
                color: #ffffff !important;
            }
            aside nav a:hover:not(.crm-active-link) {
                background-color: #cee0f4 !important;
                color: #1d4ed8 !important;
            }
            aside nav a:hover:not(.crm-active-link) svg {
                color: #2563eb !important;
            }
            aside nav a:not(.crm-active-link) {
                color: #334155 !important;
            }
            aside nav a:not(.crm-active-link) svg {
                color: #475569 !important;
            }
            aside div.uppercase {
                color: #475569 !important;
                font-weight: 700 !important;
            }

            html.dark .crm-sidebar-container,
            html.dark aside {
                background-color: #0f172a !important;
            }
            html.dark .crm-sidebar-header,
            html.dark .crm-sidebar-footer {
                background-color: #0f172a !important;
                border-color: #1e293b !important;
            }
            html.dark aside nav a.crm-active-link,
            html.dark .crm-active-link {
                background-color: #2563eb !important;
                color: #ffffff !important;
            }
            html.dark aside nav a:hover:not(.crm-active-link) {
                background-color: #1e293b !important;
                color: #f8fafc !important;
            }

            /* User Avatars & Brand Tiles: Perfect round circle, crisp white letter */
            .crm-user-avatar {
                border-radius: 9999px !important;
                color: #ffffff !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-weight: 700 !important;
                user-select: none !important;
            }

            /* Active button feedback */
            button:active:not(:disabled),
            .btn-primary:active,
            .btn-blue:active {
                transform: scale(0.98);
            }

            /* Clean primary buttons */
            .btn-primary,
            .crm-btn-primary {
                background-color: var(--crm-accent, #2563eb);
                border: 1px solid var(--crm-accent, #2563eb);
                color: #ffffff !important;
                border-radius: 8px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: background-color 0.15s ease, border-color 0.15s ease;
            }
            .btn-primary:hover,
            .crm-btn-primary:hover {
                background-color: var(--crm-accent-hover, #1d4ed8);
                border-color: var(--crm-accent-hover, #1d4ed8);
                color: #ffffff !important;
            }

            .btn-secondary,
            .crm-btn-secondary {
                background-color: #ffffff;
                border: 1px solid #cbd5e1;
                color: #334155;
                border-radius: 8px;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: background-color 0.15s ease;
            }
            html.dark .btn-secondary,
            html.dark .crm-btn-secondary {
                background-color: #1e293b;
                border-color: #334155;
                color: #f1f5f9;
            }
            .btn-secondary:hover,
            .crm-btn-secondary:hover {
                background-color: #f8fafc;
            }
            html.dark .btn-secondary:hover,
            html.dark .crm-btn-secondary:hover {
                background-color: #334155;
            }

            /* ═══════════════════════════════════════════════════════════════════════
               PREMIUM CUSTOM SEARCHABLE DROPDOWN & SELECT DESIGN
               Matches Reference (Select customer | ▲ with bottom blue line):
               - Placeholder/text "Select customer" on the left
               - Vertical divider line '|' on the right
               - Caret arrow: down when closed, up when active/open
               - Blue accent underline (border-bottom: 2px solid #2563eb)
               - Search list data ALWAYS opens DOWN (below the search bar), NEVER UP
               ═══════════════════════════════════════════════════════════════════════ */

            /* Native Select Elements */
            select,
            .crm-select,
            select.crm-select {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                border: 1px solid #cbd5e1 !important;
                border-bottom: 2px solid #2563eb !important;
                border-radius: 4px !important;
                font-size: 13px !important;
                background-color: #ffffff !important;
                color: #0f172a !important;
                padding-left: 12px !important;
                padding-right: 44px !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='36' height='24' viewBox='0 0 36 24'%3e%3cline x1='8' y1='3' x2='8' y2='21' stroke='%23cbd5e1' stroke-width='1.2'/%3e%3cpolygon points='23,15 28,9 18,9' fill='%2364748b'/%3e%3c/svg%3e") !important;
                background-position: right 0.4rem center !important;
                background-repeat: no-repeat !important;
                background-size: 36px 24px !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                width: 100% !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
            }

            select.crm-select:invalid,
            select.crm-select option[value=""] {
                color: #60a5fa !important;
            }

            html.dark select,
            html.dark .crm-select,
            html.dark select.crm-select {
                background-color: #0f172a !important;
                border-color: #334155 !important;
                border-bottom: 2px solid #3b82f6 !important;
                color: #f8fafc !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='36' height='24' viewBox='0 0 36 24'%3e%3cline x1='8' y1='3' x2='8' y2='21' stroke='%23334155' stroke-width='1.2'/%3e%3cpolygon points='23,15 28,9 18,9' fill='%2394a3b8'/%3e%3c/svg%3e") !important;
            }

            select:focus,
            .crm-select:focus {
                border-color: #cbd5e1 !important;
                border-bottom: 2px solid #1d4ed8 !important;
                outline: none !important;
                box-shadow: 0 2px 4px rgba(37, 99, 235, 0.12) !important;
            }

            /* TomSelect Searchable Dropdown Matching Reference */
            .ts-wrapper {
                position: relative !important;
                width: 100% !important;
            }

            .ts-wrapper.single .ts-control {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                border: 1px solid #cbd5e1 !important;
                border-bottom: 2px solid #2563eb !important;
                border-radius: 4px !important;
                background-color: #ffffff !important;
                background-image: none !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
                display: flex !important;
                align-items: center !important;
                padding-left: 12px !important;
                padding-right: 44px !important;
                cursor: text !important;
                position: relative !important;
            }

            html.dark .ts-wrapper.single .ts-control {
                background-color: #0f172a !important;
                border-color: #334155 !important;
                border-bottom: 2px solid #3b82f6 !important;
                color: #f8fafc !important;
            }

            /* Right-side vertical divider line '|' */
            .ts-wrapper.single .ts-control::before {
                content: "" !important;
                position: absolute !important;
                right: 32px !important;
                top: 7px !important;
                bottom: 7px !important;
                width: 1px !important;
                background-color: #cbd5e1 !important;
                z-index: 3 !important;
            }

            html.dark .ts-wrapper.single .ts-control::before {
                background-color: #334155 !important;
            }

            /* Right-side Caret Arrow: Points DOWN when closed */
            .ts-wrapper.single .ts-control::after {
                content: "" !important;
                display: block !important;
                position: absolute !important;
                top: 50% !important;
                right: 12px !important;
                margin-top: -2px !important;
                width: 0 !important;
                height: 0 !important;
                border-style: solid !important;
                border-width: 5px 5px 0 5px !important;
                border-color: #64748b transparent transparent transparent !important;
                transition: transform 0.15s ease !important;
                z-index: 3 !important;
            }

            html.dark .ts-wrapper.single .ts-control::after {
                border-color: #94a3b8 transparent transparent transparent !important;
            }

            /* Caret Arrow: Points UP when opened / active (matches user screenshot media_1791964586043.png) */
            .ts-wrapper.single.dropdown-active .ts-control::after {
                margin-top: -4px !important;
                border-width: 0 5px 5px 5px !important;
                border-color: transparent transparent #64748b transparent !important;
            }

            html.dark .ts-wrapper.single.dropdown-active .ts-control::after {
                border-color: transparent transparent #94a3b8 transparent !important;
            }

            /* Fix inner input styling: completely flat, transparent, borderless, NO nested white box */
            .ts-control input,
            .ts-control input[type="text"],
            .ts-control > input,
            .ts-wrapper .ts-control input,
            .ts-wrapper.single .ts-control input {
                border: none !important;
                border-width: 0 !important;
                outline: none !important;
                box-shadow: none !important;
                background: transparent !important;
                background-color: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                height: 100% !important;
                font-size: 13px !important;
                color: #0f172a !important;
                min-height: 0 !important;
                max-height: none !important;
                width: 100% !important;
                flex: 1 1 auto !important;
                box-sizing: border-box !important;
                border-radius: 0 !important;
                -webkit-appearance: none !important;
                appearance: none !important;
                --tw-ring-offset-shadow: 0 0 #0000 !important;
                --tw-ring-shadow: 0 0 #0000 !important;
                --tw-shadow: 0 0 #0000 !important;
            }

            .ts-control input:focus,
            .ts-control input[type="text"]:focus,
            .ts-control > input:focus,
            .ts-wrapper .ts-control input:focus {
                border: none !important;
                border-width: 0 !important;
                outline: none !important;
                box-shadow: none !important;
                ring: 0 !important;
                --tw-ring-color: transparent !important;
                --tw-ring-offset-width: 0px !important;
                --tw-ring-offset-shadow: 0 0 #0000 !important;
                --tw-ring-shadow: 0 0 #0000 !important;
                --tw-shadow: 0 0 #0000 !important;
            }

            html.dark .ts-control > input,
            html.dark .ts-wrapper .ts-control input {
                color: #f8fafc !important;
            }

            /* REMOVE LABEL FROM SEARCH BAR:
               Never show duplicate label/placeholder div inside the search bar.
               When user focuses or types, hide any .item so only clean search input is active. */
            .ts-control .placeholder,
            .ts-control .item[data-value=""],
            .ts-control .item:empty {
                display: none !important;
            }

            .ts-wrapper.single.input-active .ts-control .item,
            .ts-wrapper.single.focus .ts-control .item {
                display: none !important;
            }

            /* Selected item when not actively searching */
            .ts-wrapper.single .ts-control .item {
                font-size: 13px !important;
                color: #0f172a !important;
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            html.dark .ts-wrapper.single .ts-control .item {
                color: #f8fafc !important;
            }

            /* Native input placeholder styling */
            .ts-wrapper.single .ts-control input::placeholder {
                color: #60a5fa !important;
                font-size: 13px !important;
                opacity: 0.9 !important;
            }

            .ts-wrapper.single.input-active .ts-control input::placeholder,
            .ts-wrapper.single.focus .ts-control input::placeholder {
                color: #94a3b8 !important;
                opacity: 0.5 !important;
            }

            html.dark .ts-wrapper.single .ts-control input::placeholder {
                color: #60a5fa !important;
                opacity: 0.8 !important;
            }

            /* Hide any clear button 'x' so it stays clean */
            .ts-wrapper .clear-button {
                display: none !important;
            }

            /* CRITICAL: List of search data MUST ALWAYS BE PLACED ON THE DOWN (below the search bar), NEVER UP */
            .ts-dropdown,
            .ts-dropdown.dropdown-up,
            .ts-dropdown.dropdown-down {
                position: absolute !important;
                top: 100% !important;
                bottom: auto !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                margin-top: 3px !important;
                margin-bottom: 0 !important;
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.05) !important;
                z-index: 99999 !important;
                max-height: 240px !important;
                overflow: hidden !important;
            }

            html.dark .ts-dropdown,
            html.dark .ts-dropdown.dropdown-up,
            html.dark .ts-dropdown.dropdown-down {
                background: #0f172a !important;
                border-color: #334155 !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
            }

            .ts-dropdown .ts-dropdown-content {
                max-height: 230px !important;
                overflow-y: auto !important;
                padding: 4px 0 !important;
            }

            .ts-dropdown .option {
                padding: 8px 12px !important;
                font-size: 13px !important;
                color: #1e293b !important;
                cursor: pointer !important;
                border-bottom: 1px solid #f1f5f9 !important;
                transition: background-color 0.1s ease !important;
            }

            .ts-dropdown .option:last-child {
                border-bottom: none !important;
            }

            .ts-dropdown .option:hover,
            .ts-dropdown .option.active {
                background-color: #eff6ff !important;
                color: #1d4ed8 !important;
            }

            html.dark .ts-dropdown .option {
                color: #f1f5f9 !important;
                border-bottom-color: #1e293b !important;
            }

            html.dark .ts-dropdown .option:hover,
            html.dark .ts-dropdown .option.active {
                background-color: #1e293b !important;
                color: #60a5fa !important;
            }

            .ts-dropdown .no-results {
                padding: 10px 12px !important;
                font-size: 13px !important;
                color: #64748b !important;
                background: transparent !important;
            }

            /* Also style table search inputs to have the same sleek bottom accent */
            .crm-search-bar,
            .search-input-box,
            input[type="search"],
            input[name="search"]:not([type="hidden"]) {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                border: 1px solid #cbd5e1 !important;
                border-bottom: 2px solid #2563eb !important;
                border-radius: 4px !important;
                font-size: 13px !important;
                background-color: #ffffff !important;
                color: #0f172a !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
                transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
            }

            html.dark .crm-search-bar,
            html.dark .search-input-box,
            html.dark input[type="search"],
            html.dark input[name="search"]:not([type="hidden"]) {
                background-color: #0f172a !important;
                border-color: #334155 !important;
                border-bottom: 2px solid #3b82f6 !important;
                color: #f8fafc !important;
            }

            .crm-search-bar:focus,
            .search-input-box:focus,
            input[type="search"]:focus,
            input[name="search"]:not([type="hidden"]):focus {
                border-color: #cbd5e1 !important;
                border-bottom: 2px solid #1d4ed8 !important;
                outline: none !important;
                box-shadow: 0 2px 4px rgba(37, 99, 235, 0.12) !important;
            }
        </style>

        <script>
            // Precision IT Systems Global CRM State & Shortcuts Engine
            window.crmSidebar = {
                get collapsed() {
                    return localStorage.getItem('crm_sidebar_collapsed') === 'true';
                },
                set collapsed(val) {
                    localStorage.setItem('crm_sidebar_collapsed', val ? 'true' : 'false');
                },
                toggle() {
                    const next = !this.collapsed;
                    this.collapsed = next;
                    try {
                        if (window.Alpine && window.Alpine.store && window.Alpine.store('sidebar')) {
                            window.Alpine.store('sidebar').collapsed = next;
                        }
                    } catch(e) {}
                    window.dispatchEvent(new CustomEvent('sidebar-collapsed-changed', { detail: { collapsed: next } }));
                    return next;
                }
            };
            window.toggleSidebarCollapsed = function() {
                return window.crmSidebar.toggle();
            };

            window.crmShortcuts = {
                toggle() {
                    window.dispatchEvent(new CustomEvent('shortcuts-toggle-action'));
                },
                open() {
                    window.dispatchEvent(new CustomEvent('shortcuts-open'));
                },
                close() {
                    window.dispatchEvent(new CustomEvent('shortcuts-close'));
                }
            };
            window.crmOpenShortcuts = function() {
                window.crmShortcuts.open();
            };
            window.crmToggleShortcuts = function() {
                return window.crmShortcuts.toggle();
            };

            if (typeof window.crmToggleTheme !== 'function') {
                window.crmToggleTheme = function() {
                    if (typeof window.applyCrmMode === 'function') {
                        const isCurrentlyDark = document.documentElement.classList.contains('dark');
                        return window.applyCrmMode(isCurrentlyDark ? 'day' : 'night', true);
                    }
                    const isDark = document.documentElement.classList.toggle('dark');
                    return isDark;
                };
            }

            // Bulletproof keyboard shortcuts listener (Active on ALL pages)
            window.addEventListener('keydown', function(e) {
                const activeTag = document.activeElement ? document.activeElement.tagName.toUpperCase() : '';
                const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag) || document.activeElement?.isContentEditable;

                // 1. Ctrl+B / Cmd+B: Toggle Sidebar (Always active across all pages)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.crmSidebar.toggle();
                    return;
                }

                // 2. Ctrl+K / Cmd+K: Focus Global Search (Always active)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.innerWidth < 768) {
                        window.dispatchEvent(new CustomEvent('open-mobile-search'));
                    } else {
                        const searchInput = document.querySelector('[x-ref="searchInput"], #job-search, input[name="search"], #searchModalInput');
                        if (searchInput) {
                            searchInput.focus();
                            searchInput.select?.();
                        }
                    }
                    return;
                }

                // 3. Shortcuts Modal: '?' (when not typing in form field) OR Ctrl+/
                const isQuestionKey = (e.key === '?' || (e.shiftKey && (e.key === '/' || e.code === 'Slash')));
                const isCtrlSlash = (e.ctrlKey || e.metaKey) && (e.key === '/' || e.code === 'Slash');
                if (isCtrlSlash || (isQuestionKey && !isInput)) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.crmShortcuts.toggle();
                    return;
                }

                // 4. Escape: Close open modals, search popups, mobile drawer
                if (e.key === 'Escape') {
                    window.dispatchEvent(new CustomEvent('shortcuts-close'));
                    window.dispatchEvent(new CustomEvent('close-all-modals'));
                    const productDropdown = document.getElementById('quotation-product-dropdown');
                    if (productDropdown) productDropdown.classList.add('hidden');
                    return;
                }

                // 5. Alt+T: Toggle Theme (Dark / Light)
                if (e.altKey && (e.key === 't' || e.key === 'T')) {
                    e.preventDefault();
                    window.crmToggleTheme();
                    return;
                }

                // 6. Alt Navigation Shortcuts (when not typing in form field):
                if (!isInput && e.altKey) {
                    if (e.key === 'd' || e.key === 'D') {
                        e.preventDefault();
                        window.location.href = '{{ auth()->user()->role === 'technician' ? route('technician.dashboard') : route('dashboard') }}';
                    } else if (e.key === 'a' || e.key === 'A') {
                        e.preventDefault();
                        window.location.href = '{{ route('attendance.index') }}';
                    } else if (e.key === 'l' || e.key === 'L') {
                        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                            e.preventDefault();
                            window.location.href = '{{ route('leads.index') }}';
                        @endif
                    }
                }
            });
        </script>
    </head>
    <body class="h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#0b1120] selection:bg-blue-600 selection:text-white" 
          x-data="{ 
              sidebarOpen: false, 
              sidebarCollapsed: window.crmSidebar ? window.crmSidebar.collapsed : false,
              toggleSidebarCollapsed() {
                  this.sidebarCollapsed = window.crmSidebar.toggle();
              },
              shortcutsModalOpen: false,
              mobileNavOpen: false,
              isDark: document.documentElement.classList.contains('dark'),
              toggleTheme() {
                  this.isDark = window.crmToggleTheme();
              }
          }"
          @theme-mode-changed.window="isDark = $event.detail.isDark"
          @theme-changed.window="if ($event.detail && typeof $event.detail.isDark === 'boolean') isDark = $event.detail.isDark"
          @sidebar-collapsed-changed.window="sidebarCollapsed = $event.detail.collapsed"
          @shortcuts-toggle-action.window="shortcutsModalOpen = !shortcutsModalOpen"
          @shortcuts-toggle.window="shortcutsModalOpen = (typeof $event.detail?.open === 'boolean') ? $event.detail.open : !shortcutsModalOpen"
          @shortcuts-open.window="shortcutsModalOpen = true"
          @shortcuts-close.window="shortcutsModalOpen = false"
          @open-mobile-search.window="mobileSearchOpen = true; $nextTick(() => $refs.mobileSearchInput?.focus())">
        
            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- SECUREVISION UNIFIED ENTERPRISE SAAS CRM SUITE (ALL ROLES)        --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="crm-app-container min-h-screen flex bg-[#f8fafc] dark:bg-[#0b1120] text-slate-800 dark:text-slate-100 transition-colors duration-200">
                
                {{-- Mobile Drawer Overlay --}}
                <div x-show="sidebarOpen" 
                     x-cloak
                     x-transition:enter="transition-opacity ease-linear duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="sidebarOpen = false" 
                     class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" 
                     style="display: none;"></div>

                {{-- Sidebar Container: Fixed on mobile, sticky on desktop, 220px wide, white/slate-900, 1px solid border --}}
                <div :class="{
                        'translate-x-0': sidebarOpen,
                        '-translate-x-full lg:translate-x-0': !sidebarOpen,
                        'lg:w-20': sidebarCollapsed,
                        'lg:w-[220px]': !sidebarCollapsed
                     }"
                     class="crm-sidebar-container fixed inset-y-0 left-0 z-50 lg:sticky lg:top-0 lg:h-screen lg:z-30 w-[220px] shrink-0 transition-[width,transform] duration-200 ease-in-out flex flex-col bg-[#dbe6f6] dark:bg-[#0f172a] border-r border-[#b9d1ef] dark:border-slate-800 overflow-hidden">
                    @include('layouts.navigation')
                </div>

                {{-- Main Application Wrapper --}}
                <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#f8fafc] dark:bg-[#0b1120] transition-colors duration-200">
                    
                    {{-- Modern SaaS Top Navigation Bar matching Image 3 --}}
                    <script>
                        function globalHeader() {
                            return {
                                sidebarCollapsed: window.crmSidebar ? window.crmSidebar.collapsed : false,
                                query: '',
                                results: [],
                                open: false,
                                loading: false,
                                selectedIndex: -1,
                                mobileSearchOpen: false,
                                isDarkMode: document.documentElement.classList.contains('dark'),
                                async search() {
                                    if (this.query.trim().length < 2) {
                                        this.results = [];
                                        this.open = false;
                                        return;
                                    }
                                    this.loading = true;
                                    this.open = true;
                                    try {
                                        const res = await fetch('/api/global-search?q=' + encodeURIComponent(this.query.trim()));
                                        const data = await res.json();
                                        this.results = data.results || [];
                                    } catch (err) {
                                        this.results = [];
                                    } finally {
                                        this.loading = false;
                                    }
                                },
                                clear() {
                                    this.query = '';
                                    this.results = [];
                                    this.open = false;
                                    this.selectedIndex = -1;
                                },
                                navigate(step) {
                                    if (!this.results.length) return;
                                    this.selectedIndex = (this.selectedIndex + step + this.results.length) % this.results.length;
                                },
                                toggleThemeMode() {
                                    this.isDarkMode = window.crmToggleTheme();
                                },
                                setThemeMode(mode) {
                                    if (typeof window.applyCrmMode === 'function') {
                                        this.isDarkMode = window.applyCrmMode(mode, true);
                                    } else {
                                        this.isDarkMode = window.crmToggleTheme();
                                    }
                                },
                                selectActive() {
                                    if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
                                        window.location.href = this.results[this.selectedIndex].url;
                                    }
                                },
                                handleShortcut(isMobile) {
                                    if (isMobile) {
                                        this.mobileSearchOpen = true;
                                        this.$nextTick(() => this.$refs.mobileSearchInput?.focus());
                                    } else {
                                        this.$refs.searchInput?.focus();
                                        this.open = true;
                                    }
                                }
                            };
                        }
                    </script>

                    {{-- Fixed Top Bar: 56px high, standard white in day / #0f172a in night --}}
                    <header class="crm-topbar h-[56px] min-h-[56px] max-h-[56px] bg-white dark:bg-[#0f172a] border-b border-slate-200 dark:border-slate-800 px-5 flex items-center justify-between sticky top-0 z-10"
                            x-data="globalHeader()"
                            @theme-mode-changed.window="isDarkMode = $event.detail.isDark"
                            @theme-changed.window="if ($event.detail && typeof $event.detail.isDark === 'boolean') isDarkMode = $event.detail.isDark"
                            @sidebar-collapsed-changed.window="sidebarCollapsed = $event.detail.collapsed"
                            @keydown.escape.window="open = false; mobileSearchOpen = false"
                            @keydown.window.prevent.ctrl.k="handleShortcut(window.innerWidth < 768)"
                            @keydown.window.prevent.cmd.k="handleShortcut(window.innerWidth < 768)">
                        
                        {{-- 1. Page Name (Left) --}}
                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Mobile Hamburger Toggle --}}
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    type="button"
                                    class="lg:hidden inline-flex items-center justify-center p-1.5 rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0"
                                    aria-label="Toggle navigation menu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>

                            <span class="crm-page-title text-sm font-bold text-slate-900 dark:text-white truncate">
                                @if(request()->routeIs('dashboard')) Dashboard
                                @elseif(request()->routeIs('projects.*')) Projects
                                @elseif(request()->routeIs('leads.*')) Leads
                                @elseif(request()->routeIs('jobs.*')) Tasks &amp; Field Jobs
                                @elseif(request()->routeIs('quotations.*')) Quotations
                                @elseif(request()->routeIs('calendar.*')) Calendar
                                @elseif(request()->routeIs('invoices.*')) Invoices
                                @elseif(request()->routeIs('site-surveys.*')) Site Surveys
                                @elseif(request()->routeIs('equipment.*')) Hardware
                                @elseif(request()->routeIs('suppliers.*')) Suppliers
                                @elseif(request()->routeIs('reports.*')) Reports
                                @else Dashboard
                                @endif
                            </span>
                        </div>

                        {{-- 2. One Search Box (Center) --}}
                        <div class="hidden md:flex flex-1 items-center justify-center px-4 max-w-md mx-auto">
                            <div class="relative w-full" @click.away="open = false">
                                <div class="relative flex items-center">
                                    <svg class="w-4 h-4 text-[#666] dark:text-slate-400 absolute left-3.5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7"/>
                                        <line stroke-linecap="round" stroke-linejoin="round" x1="21" x2="16.65" y1="21" y2="16.65"/>
                                    </svg>
                                    <input type="text" 
                                           x-ref="searchInput"
                                           x-model="query"
                                           @input.debounce.250ms="search()"
                                           @focus="if(query.length >= 2) open = true"
                                           @keydown.arrow-down.prevent="navigate(1)"
                                           @keydown.arrow-up.prevent="navigate(-1)"
                                           @keydown.enter.prevent="selectActive()"
                                           placeholder="Search leads, jobs, quotes, equipment..." 
                                           style="padding-left: 2.5rem !important; height: 38px !important; border-radius: 4px !important; box-shadow: none !important;"
                                           class="crm-search-input w-full text-xs bg-white dark:bg-[#0b132b] border border-[#ccc] dark:border-slate-700 rounded text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none">
                                    
                                    {{-- Clear button or loading spinner --}}
                                    <div class="absolute right-2.5 flex items-center">
                                        <template x-if="loading">
                                            <svg class="animate-spin h-3.5 w-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!loading && query.length > 0">
                                            <button @click="clear()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold leading-none p-0.5 cursor-pointer">
                                                &times;
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Search Dropdown Results --}}
                                <div x-show="open && query.trim().length >= 2 && !mobileSearchOpen"
                                     class="absolute left-0 right-0 mt-1 w-full max-h-96 overflow-y-auto bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-700 rounded py-1 z-50 divide-y divide-slate-100 dark:divide-slate-800"
                                     style="display: none;">
                                    <template x-if="results.length > 0">
                                        <div class="py-1">
                                            <template x-for="(item, index) in results" :key="index">
                                                <a :href="item.url" 
                                                   :class="selectedIndex === index ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                                   class="flex items-start gap-3 px-3 py-2 transition text-left group block">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between gap-1">
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></p>
                                                            <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0" x-text="item.badge"></span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="item.subtitle"></p>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!loading && results.length === 0">
                                        <div class="p-4 text-center text-xs text-slate-500">
                                            No matches found
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        @php
                            $headerNotifs = \App\Services\HeaderNotificationService::getNotificationsForUser(Auth::user());
                        @endphp

                        {{-- 3. Topbar Controls: Search, Theme Toggle, Notification Bell & Pill Profile (Right) --}}
                        <div class="flex items-center gap-2.5 shrink-0">
                            {{-- Mobile search trigger icon --}}
                            <button @click="mobileSearchOpen = true; $nextTick(() => { $refs.mobileSearchInput.focus(); if (query.trim().length >= 2) open = true; })" 
                                    type="button"
                                    class="md:hidden p-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition shrink-0 cursor-pointer"
                                    title="Search">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>

                            {{-- Universal 1-Click Dark/Light Mode Button on Every Page for All Users --}}
                            <button @click="toggleThemeMode()" 
                                    type="button" 
                                    id="header-theme-toggle-btn"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 transition cursor-pointer shrink-0" 
                                    :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'" 
                                    aria-label="Toggle dark/light mode">
                                {{-- Sun Icon (shown when dark mode is active) --}}
                                <svg x-show="isDarkMode" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="5" fill="currentColor"/>
                                    <line x1="12" y1="1" x2="12" y2="3"/>
                                    <line x1="12" y1="21" x2="12" y2="23"/>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                    <line x1="1" y1="12" x2="3" y2="12"/>
                                    <line x1="21" y1="12" x2="23" y2="12"/>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                </svg>
                                {{-- Moon Icon (shown when light mode is active) --}}
                                <svg x-show="!isDarkMode" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                </svg>
                            </button>

                            {{-- Modern Notification Bell & Popover Module --}}
                            <div x-data="{
                                notifOpen: false,
                                unreadCount: {{ $headerNotifs['unread_count'] }},
                                markAllRead() {
                                    this.unreadCount = 0;
                                    try { localStorage.setItem('crm_notif_read_timestamp', Date.now()); } catch(e){}
                                }
                            }" class="relative">
                                <button @click="notifOpen = !notifOpen" 
                                        type="button" 
                                        id="header-notification-btn"
                                        class="relative inline-flex items-center justify-center w-8 h-8 rounded border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 transition cursor-pointer shrink-0" 
                                        title="Notifications & System Alerts" 
                                        aria-label="View notifications">
                                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount" 
                                          class="absolute -top-1 -right-1 flex items-center justify-center min-w-[17px] h-[17px] px-1 text-[9px] font-black text-white bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-900 leading-none">
                                    </span>
                                </button>

                                {{-- Notification Popover Dropdown --}}
                                <div x-show="notifOpen" 
                                     @click.away="notifOpen = false" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-[#0f172a] rounded-xl border border-[#ddd] dark:border-slate-700 shadow-xl py-0 z-50 overflow-hidden text-xs text-slate-700 dark:text-slate-200"
                                     style="display: none;">
                                    
                                    {{-- Popover Header --}}
                                    <div class="px-4 py-3 border-b border-[#ddd] dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white">Notifications</span>
                                            <span x-show="unreadCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400" x-text="unreadCount + ' new'"></span>
                                        </div>
                                        <button type="button" @click="markAllRead()" class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                            Mark all as read
                                        </button>
                                    </div>

                                    {{-- Items List --}}
                                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/80">
                                        @forelse($headerNotifs['items'] as $item)
                                            <a href="{{ $item['url'] }}" 
                                               @click="notifOpen = false"
                                               class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition group block">
                                                
                                                {{-- Icon badge --}}
                                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center 
                                                    @if($item['icon'] === 'ticket') bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900
                                                    @elseif($item['icon'] === 'inventory') bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900
                                                    @elseif($item['icon'] === 'job') bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900
                                                    @elseif($item['icon'] === 'log') bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900
                                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700
                                                    @endif">
                                                    @if($item['icon'] === 'ticket')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                    @elseif($item['icon'] === 'inventory')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                    @elseif($item['icon'] === 'job')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    @elseif($item['icon'] === 'log')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </div>

                                                {{-- Details --}}
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <p class="font-bold text-slate-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $item['title'] }}</p>
                                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 shrink-0">{{ $item['time'] }}</span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $item['description'] }}</p>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="p-6 text-center text-slate-400">
                                                <p class="text-xs">No pending notifications</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Footer Action Link --}}
                                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border-t border-[#ddd] dark:border-slate-800 text-center">
                                        @if(Auth::user()->isAdmin() || Auth::user()->isStaff())
                                            <a href="{{ route('alerts.index') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View Automated Alerts Engine →
                                            </a>
                                        @elseif(Auth::user()->isTechnician())
                                            <a href="{{ route('technician.dashboard') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View All Assigned Work Orders →
                                            </a>
                                        @else
                                            <a href="{{ route('dashboard') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View Operations Dashboard →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Pill-Shaped User Profile Capsule & Dropdown Menu --}}
                            <div x-data="{ userMenuOpen: false }" class="relative">
                                <button @click="userMenuOpen = !userMenuOpen" 
                                        type="button"
                                        id="header-user-profile-pill"
                                        class="flex items-center gap-2 pl-1 pr-2.5 py-1 rounded-full border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition cursor-pointer select-none shadow-2xs">
                                    @if(Auth::user()->avatar_url)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700">
                                    @else
                                        <div class="w-7 h-7 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0" style="background-color: var(--crm-accent, #2563eb);">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="text-left hidden sm:block min-w-0 pr-0.5">
                                        <span class="block text-xs font-bold text-slate-800 dark:text-slate-100 leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-medium leading-tight truncate">{{ Auth::user()->role_title }}</span>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0 transition-transform duration-150" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- User dropdown menu (contains settings, theme switcher, logout) --}}
                                <div x-show="userMenuOpen" 
                                     @click.away="userMenuOpen = false" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-60 bg-white dark:bg-[#0f172a] rounded-xl border border-[#ddd] dark:border-slate-700 shadow-xl py-1 z-50 text-xs font-medium text-slate-700 dark:text-slate-200 overflow-hidden"
                                     style="display: none;">
                                    
                                    {{-- User identity card in menu --}}
                                    <div class="px-3.5 py-2.5 border-b border-[#ddd] dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex items-center gap-3">
                                        @if(Auth::user()->avatar_url)
                                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700">
                                        @else
                                            <div class="w-9 h-9 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0" style="background-color: var(--crm-accent, #2563eb);">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-slate-900 dark:text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate leading-tight">{{ Auth::user()->email }}</p>
                                            <span class="inline-block mt-1 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-200/70 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {{ Auth::user()->role_title }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="py-1">
                                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Profile Settings &amp; Photo</span>
                                        </a>

                                        @if(Auth::user()->isAdmin())
                                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="6" y2="6"/><circle cx="8" cy="6" r="2.5"/><line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="12" y2="12"/><circle cx="16" cy="12" r="2.5"/><line stroke-linecap="round" stroke-linejoin="round" x1="4" x2="20" y1="18" y2="18"/><circle cx="10" cy="18" r="2.5"/></svg>
                                                <span>Manage User Accounts</span>
                                            </a>
                                        @endif

                                        <button type="button" @click="toggleThemeMode()" class="w-full text-left flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">
                                            <span class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                                <span>Toggle Theme</span>
                                            </span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300" x-text="isDarkMode ? 'Night' : 'Day'"></span>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('logout') }}" class="border-t border-[#ddd] dark:border-slate-800">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-3.5 py-2 text-rose-600 dark:text-rose-400 font-semibold hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            <span>Log Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Mobile Omnisearch Full-Width Overlay --}}
                        <div x-show="mobileSearchOpen"
                             x-cloak
                             class="absolute inset-0 bg-white dark:bg-[#0f172a] z-50 px-3 flex items-center gap-2 border-b border-[#ddd] dark:border-slate-800 md:hidden"
                             style="display: none;">
                            
                            <div class="relative flex-1 flex items-center">
                                <input type="text" 
                                       x-ref="mobileSearchInput"
                                       x-model="query"
                                       @input.debounce.250ms="search()"
                                       @focus="if(query.length >= 2) open = true"
                                       @keydown.arrow-down.prevent="navigate(1)"
                                       @keydown.arrow-up.prevent="navigate(-1)"
                                       @keydown.enter.prevent="selectActive()"
                                       placeholder="Search..." 
                                       style="height: 38px !important; border: 1px solid #ccc !important; border-radius: 4px !important;"
                                       class="w-full px-3 text-xs bg-white dark:bg-slate-900 border border-[#ccc] dark:border-slate-700 rounded text-slate-800 dark:text-white placeholder-slate-400">
                            </div>

                            <button @click="mobileSearchOpen = false; open = false;" 
                                    type="button" 
                                    class="px-2 py-1 rounded text-xs font-semibold text-slate-600 dark:text-slate-300 shrink-0">
                                Cancel
                            </button>
                        </div>
                    </header>

                    {{-- Main Content Area: Background #f8fafc (Clean light SaaS canvas) --}}
                    <main class="flex-1 overflow-y-auto bg-[#f8fafc] dark:bg-[#060913] p-[20px] transition-colors duration-200 pb-24 lg:pb-8">
                        <div class="max-w-[1200px] mx-auto w-full">
                            {{-- Page Heading (One h2 at top of content area, with main action button on right) --}}
                            @isset($header)
                                <div class="mb-[20px]">
                                    {{ $header }}
                                </div>
                            @endisset

                            {{ $slot }}
                        </div>
                    </main>
                </div>

            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- MOBILE APP-LIKE BOTTOM NAVIGATION DOCK (MOBILE VIEW ONLY)         --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-[#0f172a]/95 backdrop-blur-md border-t border-slate-200/90 dark:border-slate-800 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-3 py-1.5 transition-colors">
                <div class="max-w-md mx-auto flex items-center justify-between">
                    @if(auth()->user()->role === 'technician')
                        {{-- Technician Mobile Dock --}}
                        <a href="{{ route('technician.dashboard') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('technician.dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                            <span class="text-[10px] tracking-tight">Jobs</span>
                        </a>

                        <button @click="sidebarOpen = true" 
                                type="button"
                                class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="text-[10px] tracking-tight">Modules</span>
                        </button>

                        {{-- Elevated Center Action: Shift & Attendance --}}
                        <a href="{{ route('attendance.index') }}" 
                           class="-mt-5 flex flex-col items-center group">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/35 border-2 border-white dark:border-[#0f172a] active:scale-95 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Shift</span>
                        </a>

                        <a href="{{ route('projects.index') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('projects.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="13" y2="13"/></svg>
                            <span class="text-[10px] tracking-tight">Projects</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[10px] tracking-tight">Profile</span>
                        </a>

                    @else
                        {{-- Admin & Staff Mobile Dock --}}
                        <a href="{{ route('dashboard') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span class="text-[10px] tracking-tight">Home</span>
                        </a>

                        <button @click="sidebarOpen = true" 
                                type="button"
                                class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="text-[10px] tracking-tight">Modules</span>
                        </button>

                        {{-- Elevated Center Action Button (+) with Mobile Quick Action Sheet --}}
                        <div x-data="{ mobileActionOpen: false }" class="relative -mt-5">
                            <button @click="mobileActionOpen = !mobileActionOpen" 
                                    type="button"
                                    class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/35 border-2 border-white dark:border-[#0f172a] active:scale-95 transition-transform cursor-pointer"
                                    title="Quick Action">
                                <svg class="w-6 h-6 transition-transform duration-200" :class="mobileActionOpen ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                            <span class="block text-center text-[9px] font-bold text-blue-600 dark:text-blue-400 mt-0.5">Create</span>

                            {{-- Mobile Quick Action Bottom Sheet --}}
                            <div x-show="mobileActionOpen" 
                                 @click.away="mobileActionOpen = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                                 class="absolute bottom-16 left-1/2 -translate-x-1/2 w-64 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl p-2 z-50 text-xs font-semibold divide-y divide-slate-100 dark:divide-slate-800"
                                 style="display: none;">
                                <div class="px-3 py-1.5 font-bold text-slate-400 uppercase text-[10px] tracking-wider">Quick Actions</div>
                                <div class="py-1">
                                    <a href="{{ route('leads.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-blue-600">
                                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 flex items-center justify-center font-bold text-xs">+</span>
                                        New Customer Lead
                                    </a>
                                    <a href="{{ route('quotations.create-general') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-emerald-600">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center font-bold text-xs">+</span>
                                        Generate Quotation
                                    </a>
                                    <a href="{{ route('projects.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-indigo-600">
                                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center font-bold text-xs">+</span>
                                        New Installation Project
                                    </a>
                                    <a href="{{ route('attendance.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-purple-600">
                                        <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 flex items-center justify-center font-bold text-xs"></span>
                                        Attendance & Shift
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('attendance.index') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('attendance.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-[10px] tracking-tight">Roster</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[10px] tracking-tight">Profile</span>
                        </a>
                    @endif
                </div>
            </nav>

    </body>
</html>
