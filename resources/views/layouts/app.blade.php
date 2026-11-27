@php
    $cookieMode = request()->cookie('crm_mode') ?: session('crm_mode');
    $user = auth()->user();
    $userMode = $user?->theme_mode;

    // Standardize mode strings
    $cleanCookie = ($cookieMode === 'dark' || $cookieMode === 'night') ? 'night' : (($cookieMode === 'light' || $cookieMode === 'day') ? 'day' : null);
    $cleanUser   = ($userMode === 'dark' || $userMode === 'night') ? 'night' : (($userMode === 'light' || $userMode === 'day') ? 'day' : null);

    // Active mode: Cookie/session represents real-time client UI state and takes precedence over stale DB state on instant navigation/refresh
    $activeMode = $cleanCookie ?? $cleanUser ?? 'night';

    // Auto-sync database preference if the user clicked the toggle in the browser
    if ($user && $cleanCookie && $cleanCookie !== $cleanUser) {
        try {
            $user->theme_mode = $cleanCookie;
            $user->saveQuietly();
        } catch (\Throwable $e) {}
    }

    $isDarkServer = ($activeMode === 'night');

    // Top bar + browser tab title: first matching route pattern wins, so keep specific patterns above broad ones
    $crmPageTitles = [
        'dashboard' => 'Dashboard',
        'technician.*' => 'Field Work Orders',
        'calendar.*' => 'Calendar and Tasks',
        'leads.index' => 'Leads',
        'leads.*' => 'Leads & Inquiries',
        'site-surveys.*' => 'Site Surveys',
        'quotations.index' => 'Deals & Quotations',
        'quotations.*' => 'Quotations',
        'projects.*' => 'Projects',
        'sites.*' => 'Installation Sites',
        'workers.attendance.*' => 'Worker Attendance',
        'workers.*' => 'Workers',
        'wages.*' => 'Weekly Wages',
        'documents.*' => 'Ready to Invoice',
        'jobs.*' => 'Tasks & Field Jobs',
        'jcr.*' => 'Job Completion Reports',
        'equipment.*' => 'Cameras & Assets',
        'service-tickets.*' => 'Service Tickets',
        'amcs.*' => 'AMC Contracts',
        'amc-visits.*' => 'AMC Contracts',
        'rma.*' => 'RMA Claims',
        'products.*' => 'Products & Pricing',
        'purchase-orders.*' => 'Purchase Orders',
        'suppliers.*' => 'Suppliers & Vendors',
        'inventory.*' => 'Inventory',
        'employee.hub' => 'Employees',
        'attendance.*' => 'Attendance',
        'leaves.*' => 'Leave',
        'finance.hub' => 'Finance',
        'finance.analytics' => 'Salary Analytics',
        'finance.expenses.*' => 'Expense Claims',
        'finance.gst.*' => 'GST',
        'finance.job-costing.*' => 'Job Costing',
        'finance.payables.*' => 'Payables',
        'finance.receivables.*' => 'Receivables',
        'finance.payroll.*' => 'Payroll',
        'finance.petty_cash.*' => 'Petty Cash',
        'finance.salaries.*' => 'Salaries',
        'finance.*' => 'Finance',
        'invoices.*' => 'Invoices',
        'payments.*' => 'Payments',
        'payment.*' => 'Payments',
        'alerts.*' => 'Alerts',
        'analytics.*' => 'Analytics',
        'admin.users.*' => 'User Accounts',
        'profile.*' => 'Profile Settings',
    ];
    $crmPageTitle = null;
    foreach ($crmPageTitles as $pattern => $label) {
        if (request()->routeIs($pattern)) {
            $crmPageTitle = $label;
            break;
        }
    }
    $crmPageTitle ??= \Illuminate\Support\Str::headline(\Illuminate\Support\Str::before((string) request()->route()?->getName(), '.')) ?: 'Dashboard';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full max-w-full {{ $isDarkServer ? 'dark' : '' }}" data-theme="{{ $isDarkServer ? 'dark' : 'light' }}" style="background-color: var(--page-bg); color: var(--text-main);">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __($crmPageTitle) }} | {{ config('app.name', 'Precision IT Systems CRM') }}</title>

        <!-- Favicon / App Icon -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ filemtime(public_path('favicon.png')) }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
        <link rel="apple-touch-icon" href="{{ asset('icons/field-192.png') }}">

        <!-- PWA Web App Manifest & Mobile Meta Tags -->
        <link rel="manifest" href="{{ url('/field/manifest.webmanifest') }}">
        <meta name="theme-color" content="#2563eb">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="PrecisionField">

        <!-- Dynamic Logo Engine -->
        <x-crm-theme-init :userId="auth()->id()" :userAccent="auth()->user()->theme_accent" :userMode="$activeMode" :userStyle="auth()->user()->theme_style" />

        <!-- Scripts & Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            // Universal Barcode Sanitizer for all hardware scanner guns & mobile inputs
            window.cleanBarcode = function(rawCode) {
                if (!rawCode) return '';
                let val = String(rawCode).trim();
                val = val.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '').trim();
                val = val.replace(/^[\]\[][a-zA-Z0-9]{2}[:\s=_-]*/i, '').trim();
                val = val.replace(/^\(21\)[:\s=_-]*/i, '').trim();
                return val.trim();
            };

            // Cleanup candidates: thin wrappers delegating to the Alpine hardwareScannerModalComponent
            window.switchHardwareScannerTab = function(tab) {
                if (window.__hardwareScannerInstance && typeof window.__hardwareScannerInstance.switchTab === 'function') {
                    window.__hardwareScannerInstance.switchTab(tab || 'mobile');
                }
            };

            window.openHardwareScannerModal = function(tabOrContext, extraContext) {
                var tab = 'auto';
                var context = {};
                if (tabOrContext && typeof tabOrContext === 'object') {
                    context = tabOrContext;
                    tab = context.tab || 'auto';
                } else {
                    tab = tabOrContext || 'auto';
                    if (extraContext && typeof extraContext === 'object') {
                        context = extraContext;
                    }
                }
                if (window.__hardwareScannerInstance && typeof window.__hardwareScannerInstance.openModal === 'function') {
                    window.__hardwareScannerInstance.openModal(tab, context);
                } else {
                    window.dispatchEvent(new CustomEvent('open-hardware-scanner', { detail: Object.assign({ tab: tab }, context) }));
                }
            };

            window.closeHardwareScannerModal = function() {
                if (window.__hardwareScannerInstance && typeof window.__hardwareScannerInstance.closeModal === 'function') {
                    window.__hardwareScannerInstance.closeModal();
                }
            };

            window.startMobileCamera = function(isUserGesture) {
                if (window.__hardwareScannerInstance && typeof window.__hardwareScannerInstance.startMobileCamera === 'function') {
                    window.__hardwareScannerInstance.startMobileCamera(isUserGesture !== false);
                }
            };
        </script>

        {{-- Technicians can switch their pages to Tamil, and fill text boxes by voice or the Tamil keyboard --}}
        @if(auth()->user()->canChooseLanguage())
            <x-tamil-font />
            <x-tamil-input-script />
        @endif

        <style>
            /* Colour, size and corner tokens live in resources/css/app.css (one source for every page) */

            html {
                font-family: var(--font-sans);
            }
            html.dark {
                color-scheme: dark;
            }

            .custom-sidebar-scroll {
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: var(--input-border) transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar {
                width: 4px;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-track {
                background: transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-thumb {
                background-color: #cbd5e1;
                border-radius: 9999px;
            }
            html.dark .custom-sidebar-scroll {
                scrollbar-width: thin;
                scrollbar-color: var(--input-border) transparent;
            }
            html.dark .custom-sidebar-scroll::-webkit-scrollbar-thumb {
                background-color: #334155;
            }
            html.dark .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
                background-color: #475569;
            }

            /* Global Scrollbar Elimination for Pills & Tabs */
            .no-scrollbar::-webkit-scrollbar,
            .no-scrollbar::-webkit-scrollbar-track,
            .no-scrollbar::-webkit-scrollbar-thumb,
            .no-scrollbar::-webkit-scrollbar-corner {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                background: transparent !important;
                border: none !important;
                opacity: 0 !important;
            }
            .no-scrollbar {
                -ms-overflow-style: none !important;
                scrollbar-width: none !important;
                -webkit-overflow-scrolling: touch !important;
            }

            :root {
                --crm-accent: #2563eb;
                --crm-accent-hover: #1d4ed8;
            }

            /* Modern Stat Card Surface with Crisp Single 1px Dark Border Line (Matching Image 4) */
            .stat-card,
            .saas-card,
            .crm-card,
            .crm-card-surface,
            .pip-card,
            .pg-card,
            .card {
                background: var(--card-bg) !important;
                color: var(--text-main) !important;
                border: 1px solid var(--card-border) !important;
                border-radius: 10px;
                padding: 1rem 1.25rem;
                transition: all 0.2s ease-in-out;
            }
            html.dark .stat-card,
            html.dark .saas-card,
            html.dark .crm-card,
            html.dark .crm-card-surface,
            html.dark .pip-card,
            html.dark .pg-card,
            html.dark .card {
                background: var(--card-bg) !important;
                color: var(--text-main) !important;
                border: 1px solid var(--card-border) !important;
            }

            /* Universal Tailwind Utility Overrides to Crisp 1px Border (Color ONLY - never force 4-sided border width!) */
            .border-\[\#ddd\],
            .border-\[\#dddddd\],
            .border-\[\#cbd5e1\],
            .border-\[\#e2e8f0\],
            .border-slate-100,
            .border-slate-200,
            .border-slate-300,
            .border-gray-100,
            .border-gray-200,
            .border-gray-300 {
                border-color: var(--card-border) !important;
            }
            html.dark .border-\[\#ddd\],
            html.dark .border-\[\#dddddd\],
            html.dark .border-\[\#cbd5e1\],
            html.dark .border-\[\#e2e8f0\],
            html.dark .border-slate-100,
            html.dark .border-slate-200,
            html.dark .border-slate-300,
            html.dark .border-gray-100,
            html.dark .border-gray-200,
            html.dark .border-gray-300,
            html.dark .dark\:border-slate-800,
            html.dark .dark\:border-slate-700 {
                border-color: var(--card-border) !important;
            }

            /* Directional Border Guards: Ensure headers, dividers & tabs never turn into 4-sided boxed cages */
            .border-b, [class*="border-b-"] { border-top-width: 0 !important; border-left-width: 0 !important; border-right-width: 0 !important; }
            .border-t, [class*="border-t-"] { border-bottom-width: 0 !important; border-left-width: 0 !important; border-right-width: 0 !important; }
            .border-l, [class*="border-l-"] { border-top-width: 0 !important; border-bottom-width: 0 !important; border-right-width: 0 !important; }
            .border-r, [class*="border-r-"] { border-top-width: 0 !important; border-bottom-width: 0 !important; border-left-width: 0 !important; }
            .border-0, .border-none { border-width: 0 !important; }

            /* Universal Form Search & Input Bars: Crisp single 1px dark borders with distinct focus ring */
            input[type="text"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            input[type="search"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            input[type="email"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            input[type="password"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            input[type="number"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            input[type="tel"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            input[type="date"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            select:not([class*="border-0"]):not([class*="border-b"]),
            textarea:not([class*="border-0"]):not([class*="border-b"]),
            .crm-input,
            .crm-select,
            .crm-search-input,
            .crm-cal-input,
            .crm-cal-select,
            .ts-control {
                border: 1px solid var(--card-border) !important;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            html.dark input[type="text"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            html.dark input[type="search"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            html.dark input[type="email"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            html.dark input[type="password"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            html.dark input[type="number"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            html.dark input[type="tel"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            html.dark input[type="date"]:not([class*="bg-transparent"]):not([class*="border-0"]):not([class*="border-b"]),
            html.dark select:not([class*="border-0"]):not([class*="border-b"]),
            html.dark textarea:not([class*="border-0"]):not([class*="border-b"]),
            html.dark .crm-input,
            html.dark .crm-select,
            html.dark .crm-search-input,
            html.dark .crm-cal-input,
            html.dark .crm-cal-select,
            html.dark .ts-control {
                border: 1px solid var(--input-border) !important;
            }

            /* Generous, comfortable font-to-border space for inputs and selects */
            input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="color"]):not([type="range"]):not(.ts-control input):not(.ts-wrapper input):not([type="search"]):not([name="search"]):not(.search-icon-input):not(.pl-9):not(.pl-10):not(.pl-11):not(.pl-12):not(.pl-14),
            .crm-input,
            .crm-cal-input {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            select:not(.tomselected),
            .crm-select,
            .crm-cal-select {
                padding-left: 12px !important;
                padding-right: 32px !important;
            }

            textarea,
            .crm-textarea {
                padding: 10px 14px !important;
                line-height: 1.5 !important;
            }

            /* Dedicated Crisp Solid Outline on Search Bars, Combobox Triggers, Selects & Searchable Inputs (Zero Glow / Zero Blur) */
            .crm-combobox-trigger:focus,
            .crm-combobox-trigger:focus-visible,
            .crm-combobox-trigger:active,
            .crm-combobox-trigger[aria-expanded="true"],
            .crm-searchable-combobox.is-open .crm-combobox-trigger,
            .crm-combobox-search-input:focus,
            .crm-combobox-search-input:focus-visible,
            input[type="search"]:focus,
            input[type="search"]:focus-visible,
            input[placeholder*="Search" i]:focus,
            input[placeholder*="Search" i]:focus-visible,
            input[placeholder*="search" i]:focus,
            input[placeholder*="search" i]:focus-visible,
            input[name*="search" i]:focus,
            input[id*="search" i]:focus,
            .crm-search-input:focus,
            .crm-search-input:focus-visible,
            .crm-cal-select:focus,
            .crm-cal-select:active,
            select:focus,
            select:active,
            select.crm-select:focus,
            select.crm-select:active,
            .crm-select:focus,
            .crm-select:active {
                border-color: var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
                outline: 2px solid var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
                outline-offset: -1px !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                --tw-ring-color: transparent !important;
                --tw-ring-shadow: 0 0 #0000 !important;
                --tw-ring-offset-shadow: 0 0 #0000 !important;
                --tw-shadow: 0 0 #0000 !important;
            }
            .crm-combobox-trigger,
            .crm-combobox-trigger:hover,
            .crm-combobox-trigger:focus,
            .crm-combobox-trigger:focus-visible,
            .crm-combobox-trigger:active,
            .crm-combobox-trigger[aria-expanded="true"],
            .crm-searchable-combobox.is-open .crm-combobox-trigger,
            select:active,
            select.crm-select:active,
            .crm-select:active {
                background-color: var(--card-bg, #ffffff) !important;
                background: var(--card-bg, #ffffff) !important;
                background-image: none !important;
                transform: none !important;
            }
            html.dark .crm-combobox-trigger,
            html.dark .crm-combobox-trigger:hover,
            html.dark .crm-combobox-trigger:focus,
            html.dark .crm-combobox-trigger:focus-visible,
            html.dark .crm-combobox-trigger:active,
            html.dark .crm-combobox-trigger[aria-expanded="true"],
            html.dark .crm-searchable-combobox.is-open .crm-combobox-trigger,
            html.dark select:active,
            html.dark select.crm-select:active,
            html.dark .crm-select:active {
                background-color: #0f172a !important;
                background: #0f172a !important;
                background-image: none !important;
                transform: none !important;
            }

            /* Standard Form Inputs Focus */
            input:focus:not([class*="border-0"]):not([type="search"]):not([placeholder*="Search"]):not([placeholder*="search"]):not([name*="search"]):not([id*="search"]),
            textarea:focus:not([class*="border-0"]),
            .crm-input:focus:not(.crm-search-input),
            .crm-cal-input:focus,
            .ts-control.focus,
            .ts-control:focus-within {
                border-color: var(--crm-accent, #2563eb) !important;
                box-shadow: 0 0 0 3px rgba(var(--crm-accent-rgb, 37, 99, 235), 0.28) !important;
                outline: none !important;
            }
            html.dark input:focus:not([class*="border-0"]):not([type="search"]):not([placeholder*="Search"]):not([placeholder*="search"]):not([name*="search"]):not([id*="search"]),
            html.dark textarea:focus:not([class*="border-0"]),
            html.dark .crm-input:focus:not(.crm-search-input),
            html.dark .crm-cal-input:focus,
            html.dark .ts-control.focus,
            html.dark .ts-control:focus-within {
                border-color: color-mix(in srgb, var(--crm-accent, #2563eb) 80%, #fff) !important;
                box-shadow: 0 0 0 3px rgba(var(--crm-accent-rgb, 37, 99, 235), 0.35) !important;
                outline: none !important;
            }

            /* Dark Mode Universal Action & Detail Icons: Clean Luminous White & Light Slate (Strictly Never Black in Dark Mode) */
            html.dark a svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg),
            html.dark button svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg),
            html.dark .inline-flex svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]),
            html.dark a[href^="tel:"] svg,
            html.dark a[href*="maps"] svg,
            html.dark a[title*="Call"] svg,
            html.dark a[title*="Map"] svg,
            html.dark svg.text-black,
            html.dark svg[class*="text-black"],
            html.dark svg[stroke="#000000"],
            html.dark svg[stroke="#000"],
            html.dark svg[stroke="black"],
            html.dark table td svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]),
            html.dark .detail-table svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]) {
                stroke: #e2e8f0 !important;
                color: #e2e8f0 !important;
                fill: none !important;
            }
            html.dark a svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg) *,
            html.dark button svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg) *,
            html.dark .inline-flex svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]) *,
            html.dark a[href^="tel:"] svg *,
            html.dark a[href*="maps"] svg *,
            html.dark a[title*="Call"] svg *,
            html.dark a[title*="Map"] svg *,
            html.dark svg.text-black *,
            html.dark svg[class*="text-black"] *,
            html.dark svg[stroke="#000000"] *,
            html.dark svg[stroke="#000"] *,
            html.dark svg[stroke="black"] *,
            html.dark table td svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]) *,
            html.dark .detail-table svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]) * {
                stroke: #e2e8f0 !important;
                fill: none !important;
            }

            /* Absolute Dark Mode Call & Action SVG Visibility (Always luminous currentColor, never dark/black) */
            html.dark a[href^="tel:"] svg,
            html.dark a[title*="Call"] svg,
            html.dark a[title*="Call Technician"] svg,
            html.dark a[href*="maps"] svg,
            html.dark a[title*="Map"] svg {
                stroke: currentColor !important;
                color: inherit !important;
                fill: none !important;
            }
            html.dark a[href^="tel:"] svg *,
            html.dark a[title*="Call"] svg *,
            html.dark a[title*="Call Technician"] svg *,
            html.dark a[href*="maps"] svg *,
            html.dark a[title*="Map"] svg * {
                stroke: currentColor !important;
                fill: none !important;
            }

            /* Universal Data Tables: Crisp Single 1px Border System (Fixes Overlapping / Double Borders) */
            table,
            table.min-w-full,
            table.w-full,
            .leads-table,
            .jobs-table,
            .q-table,
            .p-table,
            .crm-table,
            .detail-table,
            .surveys-table,
            .inv-table,
            .eq-table,
            .amc-table,
            .users-table,
            .suppliers-table,
            .products-table,
            .data-table,
            .pg-table,
            .saas-table {
                border: none !important;
                border-collapse: collapse !important;
                border-spacing: 0 !important;
                width: 100% !important;
                margin: 0 !important;
            }

            /* Containers around tables provide the clean, single 1px outer frame (Image 4 standard) */
            .pg-card,
            .table-container,
            .saas-card {
                border: 1px solid var(--card-border) !important;
                border-radius: 8px !important;
                overflow: hidden !important;
            }
            html.dark .pg-card,
            html.dark .table-container,
            html.dark .saas-card {
                border-color: var(--card-border) !important;
            }

            /* Inner table wrappers must NEVER add duplicate borders inside the card */
            .overflow-x-auto,
            div[style*="overflow-x:auto"],
            div[style*="overflow-x: auto"],
            .pg-card > div:has(> table),
            .table-container > div:has(> table) {
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Single 1px divider between filter toolbar and table header */
            .filter-bar,
            form.filter-bar,
            .crm-filter-bar {
                border-top: none !important;
                border-left: none !important;
                border-right: none !important;
                border-bottom: 1px solid var(--table-border) !important;
            }
            html.dark .filter-bar,
            html.dark form.filter-bar,
            html.dark .crm-filter-bar {
                border-bottom: 1px solid var(--table-border) !important;
            }

            /* Single 1px top divider for pagination footer inside card */
            .pg-links,
            .pagination-wrap {
                border-bottom: none !important;
                border-left: none !important;
                border-right: none !important;
                border-top: 1px solid var(--card-border) !important;
            }
            html.dark .pg-links,
            html.dark .pagination-wrap {
                border-top: 1px solid var(--card-border) !important;
            }

            table thead,
            table thead tr {
                border-top: none !important;
                border-bottom: 1px solid var(--table-border) !important;
                background-color: var(--table-header-bg) !important;
            }
            html.dark table thead,
            html.dark table thead tr {
                border-top: none !important;
                border-bottom: 1px solid var(--table-border) !important;
                background-color: var(--table-header-bg) !important;
            }

            table th {
                border-top: none !important;
                border-bottom: 1px solid var(--table-border) !important;
                background-color: var(--table-header-bg) !important;
                color: var(--text-main) !important;
                font-weight: 600 !important;
                padding: 10px 16px;
            }
            html.dark table th {
                border-top: none !important;
                border-bottom: 1px solid var(--table-border) !important;
                background-color: var(--table-header-bg) !important;
                color: var(--text-main) !important;
                font-weight: 600 !important;
                padding: 10px 16px;
            }

            table tbody tr,
            table tr {
                border-bottom: none !important;
            }
            table td {
                border-bottom: 1px solid var(--table-border) !important;
                color: var(--text-main) !important;
                padding: 11px 16px;
            }
            html.dark table tbody tr,
            html.dark table tr {
                border-bottom: none !important;
            }
            html.dark table td {
                border-bottom: 1px solid var(--table-border) !important;
                color: var(--text-main) !important;
                padding: 11px 16px;
            }

            /* No right border on last column */
            table th:last-child,
            table td:last-child {
                border-right: none !important;
            }

            /* Never double the bottom border on the last table row */
            table tbody tr:last-child,
            table tbody tr:last-child td {
                border-bottom: none !important;
            }

            /* Single 1px vertical column dividers & horizontal cell dividers (cleanly collapsed, no double outer strokes) */
            table th,
            table td,
            .leads-table th, .leads-table td,
            .jobs-table th, .jobs-table td,
            .q-table th, .q-table td,
            .p-table th, .p-table td,
            .crm-table th, .crm-table td,
            .detail-table th, .detail-table td {
                border-right: 1px solid var(--table-border) !important;
                border-bottom: 1px solid var(--table-border) !important;
            }
            html.dark table th,
            html.dark table td,
            html.dark .leads-table th, html.dark .leads-table td,
            html.dark .jobs-table th, html.dark .jobs-table td,
            html.dark .q-table th, html.dark .q-table td,
            html.dark .p-table th, html.dark .p-table td,
            html.dark .crm-table th, html.dark .crm-table td,
            html.dark .detail-table th, html.dark .detail-table td {
                border-right: 1px solid var(--table-border) !important;
                border-bottom: 1px solid var(--table-border) !important;
            }
            table th:first-child,
            table td:first-child {
                border-left: none !important;
            }
            table th:last-child,
            table td:last-child {
                border-right: none !important;
            }
            table tbody tr:last-child td,
            table tr:last-child td {
                border-bottom: none !important;
            }

            /* Omnisearch & Search Bar Border System */
            .crm-search-input,
            header input[type="text"],
            input[placeholder*="Search"] {
                border: 1px solid var(--card-border) !important;
                border-color: var(--card-border) !important;
                border-radius: 6px !important;
            }
            html.dark .crm-search-input,
            html.dark header input[type="text"],
            html.dark input[placeholder*="Search"] {
                border: 1px solid var(--input-border) !important;
                border-color: var(--input-border) !important;
            }

            /* Calendar Grid Cell Borders */
            .grid-cols-7 > div[class*="border-b"] {
                border-bottom: 1px solid var(--table-border) !important;
            }
            .grid-cols-7 > div[class*="border-r"] {
                border-right: 1px solid var(--table-border) !important;
            }
            html.dark .grid-cols-7 > div[class*="border-b"] {
                border-bottom: 1px solid var(--table-border) !important;
            }
            html.dark .grid-cols-7 > div[class*="border-r"] {
                border-right: 1px solid var(--table-border) !important;
            }

            /* Universal Divide Lines */
            .divide-y > * + *,
            .divide-\[\#eee\] > * + *,
            .divide-\[\#ddd\] > * + *,
            .divide-slate-100 > * + *,
            .divide-slate-200 > * + * {
                border-top: 1px solid var(--card-border) !important;
            }
            html.dark .divide-y > * + *,
            html.dark .divide-\[\#eee\] > * + *,
            html.dark .divide-\[\#ddd\] > * + *,
            html.dark .divide-slate-800 > * + *,
            html.dark .divide-slate-700 > * + * {
                border-top: 1px solid var(--card-border) !important;
            }

            /* Header Bar: Defined Dark Border Bottom & Fixed Sticky Positioning */
            header.crm-topbar,
            .crm-topbar {
                background-color: var(--card-bg) !important;
                border-bottom: 1px solid var(--table-border) !important;
                position: sticky !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 30 !important;
                flex-shrink: 0 !important;
                width: 100% !important;
            }
            html.dark header.crm-topbar,
            html.dark .crm-topbar {
                background-color: var(--card-bg) !important;
                border-bottom: 1px solid var(--table-border) !important;
                position: sticky !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 30 !important;
                flex-shrink: 0 !important;
                width: 100% !important;
            }

            /* Dashboard & Page Name Font in Header */
            .crm-topbar .crm-page-title,
            .crm-topbar span.font-bold {
                font-size: 15px !important;
                font-weight: 700 !important;
                color: var(--text-main) !important;
                letter-spacing: -0.01em !important;
            }
            html.dark .crm-topbar .crm-page-title,
            html.dark .crm-topbar span.font-bold {
                color: var(--text-main) !important;
            }

            /* Mobile menu & search toggle icons */
            .crm-topbar button[aria-label="Toggle navigation menu"],
            .crm-topbar button[title="Search"] {
                color: #334155 !important;
            }
            html.dark .crm-topbar button[aria-label="Toggle navigation menu"],
            html.dark .crm-topbar button[title="Search"] {
                color: var(--text-muted) !important;
            }

            /* Crisp dark borders for search bar, theme toggle, notification, and profile */
            .crm-topbar input[type="text"],
            .crm-topbar .crm-search-input {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--card-border) !important;
                color: var(--text-main) !important;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            .crm-topbar input[type="text"]:focus,
            .crm-topbar .crm-search-input:focus {
                border-color: var(--crm-accent, #2563eb) !important;
                box-shadow: 0 0 0 3px rgba(var(--crm-accent-rgb, 37, 99, 235), 0.28) !important;
                outline: none !important;
            }
            html.dark .crm-topbar input[type="text"],
            html.dark .crm-topbar .crm-search-input {
                background-color: #0b132b !important;
                border: 1px solid var(--input-border) !important;
                color: var(--text-main) !important;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            html.dark .crm-topbar input[type="text"]:focus,
            html.dark .crm-topbar .crm-search-input:focus {
                border-color: color-mix(in srgb, var(--crm-accent, #2563eb) 80%, #fff) !important;
                box-shadow: 0 0 0 3px rgba(var(--crm-accent-rgb, 37, 99, 235), 0.35) !important;
                outline: none !important;
            }

            /* Dark Mode Universal Action & Detail Icons: Clean Luminous White & Light Slate (Strictly Never Black in Dark Mode) */
            html.dark a svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg),
            html.dark button svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg),
            html.dark .inline-flex svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]),
            html.dark a[href^="tel:"] svg,
            html.dark a[href*="maps"] svg,
            html.dark a[title*="Call"] svg,
            html.dark a[title*="Map"] svg,
            html.dark svg.text-black,
            html.dark svg[class*="text-black"],
            html.dark svg[stroke="#000000"],
            html.dark svg[stroke="#000"],
            html.dark svg[stroke="black"],
            html.dark table td svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]),
            html.dark .detail-table svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]) {
                stroke: #e2e8f0 !important;
                color: #e2e8f0 !important;
                fill: none !important;
            }
            html.dark a svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg) *,
            html.dark button svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]):not(.crm-brand-logo svg):not(.theme-toggle svg) *,
            html.dark .inline-flex svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-green"]):not([class*="text-amber"]):not([class*="text-yellow"]):not([class*="text-rose"]):not([class*="text-red"]):not([class*="text-blue"]):not([class*="text-indigo"]) *,
            html.dark a[href^="tel:"] svg *,
            html.dark a[href*="maps"] svg *,
            html.dark a[title*="Call"] svg *,
            html.dark a[title*="Map"] svg *,
            html.dark svg.text-black *,
            html.dark svg[class*="text-black"] *,
            html.dark svg[stroke="#000000"] *,
            html.dark svg[stroke="#000"] *,
            html.dark svg[stroke="black"] *,
            html.dark table td svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]) *,
            html.dark .detail-table svg:not(.icon-solid):not([class*="text-emerald"]):not([class*="text-rose"]):not([class*="text-amber"]):not([class*="text-green"]) * {
                stroke: #e2e8f0 !important;
                fill: none !important;
            }

            /* Absolute Dark Mode Call & Action SVG Visibility (Always luminous currentColor, never dark/black) */
            html.dark a[href^="tel:"] svg,
            html.dark a[title*="Call"] svg,
            html.dark a[title*="Call Technician"] svg,
            html.dark a[href*="maps"] svg,
            html.dark a[title*="Map"] svg {
                stroke: currentColor !important;
                color: inherit !important;
                fill: none !important;
            }
            html.dark a[href^="tel:"] svg *,
            html.dark a[title*="Call"] svg *,
            html.dark a[title*="Call Technician"] svg *,
            html.dark a[href*="maps"] svg *,
            html.dark a[title*="Map"] svg * {
                stroke: currentColor !important;
                fill: none !important;
            }

            .crm-topbar #header-theme-toggle-btn {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--card-border) !important;
                color: var(--text-main) !important;
            }
            html.dark .crm-topbar #header-theme-toggle-btn {
                background-color: var(--table-header-bg) !important;
                border: 1px solid var(--input-border) !important;
                color: #e2e8f0 !important;
            }

            .crm-topbar #header-notification-btn {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--card-border) !important;
                color: var(--text-main) !important;
            }
            html.dark .crm-topbar #header-notification-btn {
                background-color: var(--table-header-bg) !important;
                border: 1px solid var(--input-border) !important;
                color: #e2e8f0 !important;
            }

            .crm-topbar #header-user-profile-pill {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--card-border) !important;
            }
            html.dark .crm-topbar #header-user-profile-pill {
                background-color: var(--table-header-bg) !important;
                border: 1px solid var(--input-border) !important;
            }

            /* Dedicated User Profile Menu Dropdown & Action Items */
            #crm-user-panel .crm-menu-item {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: flex-start !important;
                gap: 12px !important;
                width: 100% !important;
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                padding: 0 12px !important;
                border-radius: 12px !important;
                border: none !important;
                background: transparent !important;
                color: var(--text-main) !important;
                font-size: 13px !important;
                font-weight: 500 !important;
                text-align: left !important;
                text-decoration: none !important;
                box-sizing: border-box !important;
                white-space: nowrap !important;
                transition: background-color 0.15s ease, color 0.15s ease !important;
                cursor: pointer !important;
            }
            #crm-user-panel .crm-menu-item:hover {
                background-color: var(--row-hover, rgba(0,0,0,0.04)) !important;
                color: var(--text-main) !important;
            }
            html.dark #crm-user-panel .crm-menu-item:hover {
                background-color: rgba(255, 255, 255, 0.06) !important;
            }
            #crm-user-panel .crm-menu-icon-box {
                width: 32px !important;
                height: 32px !important;
                min-width: 32px !important;
                border-radius: 8px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                flex-shrink: 0 !important;
            }
            #crm-user-panel .crm-menu-icon-box svg {
                width: 17px !important;
                height: 17px !important;
                flex-shrink: 0 !important;
            }
            #crm-user-panel .crm-menu-label {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                color: var(--text-main) !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            #crm-user-panel .crm-menu-badge {
                font-size: 10px !important;
                font-weight: 700 !important;
                padding: 2px 8px !important;
                border-radius: 9999px !important;
                margin-left: auto !important;
                flex-shrink: 0 !important;
                white-space: nowrap !important;
                letter-spacing: 0.03em !important;
            }
            #crm-user-panel .crm-menu-end-icon {
                margin-left: auto !important;
                color: var(--text-muted) !important;
                flex-shrink: 0 !important;
            }
            #crm-user-panel .crm-menu-logout:hover {
                background-color: rgba(244, 63, 94, 0.08) !important;
            }

            /* User Avatars & Brand Tiles: Perfect round circle, crisp white letter */
            .crm-user-avatar {
                border-radius: 9999px !important;
                color: var(--text-main) !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-weight: 700 !important;
                user-select: none !important;
            }

            /* Active button feedback */
            button:active:not(:disabled):not(.crm-combobox-trigger):not(.crm-select):not(.crm-input),
            .btn-primary:active,
            .btn-blue:active {
                transform: scale(0.98);
            }

            /* Clean primary buttons */
            .btn-primary,
            .crm-btn-primary {
                background-color: var(--crm-accent, #2563eb);
                border: 1px solid var(--crm-accent, #2563eb);
                color: var(--text-main) !important;
                border-radius: 6px;
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
                color: var(--text-main) !important;
            }

            .btn-secondary,
            .crm-btn-secondary {
                background-color: #ffffff;
                border: 1px solid var(--card-border) !important;
                color: var(--text-main) !important;
                border-radius: 6px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: background-color 0.15s ease, border-color 0.15s ease;
            }
            html.dark .btn-secondary,
            html.dark .crm-btn-secondary {
                background-color: #1e293b;
                border: 1px solid var(--input-border) !important;
                color: #f1f5f9 !important;
            }
            .btn-secondary:hover,
            .crm-btn-secondary:hover {
                background-color: #f1f5f9;
                border-color: var(--text-main) !important;
            }
            html.dark .btn-secondary:hover,
            html.dark .crm-btn-secondary:hover {
                background-color: #334155;
                border-color: var(--text-muted) !important;
            }

            /* ═══════════════════════════════════════════════════════════════════════
               PREMIUM CUSTOM SEARCHABLE DROPDOWN & SELECT DESIGN
               ═══════════════════════════════════════════════════════════════════════ */

            /* Native Select Elements - Ergonomic Medium Height (38px) with Clean 1px Border */
            select,
            .crm-select,
            select.crm-select {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                border: 1px solid var(--card-border) !important;
                border-radius: 8px !important;
                font-size: 13px !important;
                font-weight: 500 !important;
                background-color: var(--card-bg) !important;
                color: var(--text-main) !important;
                padding-left: 12px !important;
                padding-right: 32px !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 20 20' fill='%231e293b'%3e%3cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3e%3c/svg%3e") !important;
                background-position: right 10px center !important;
                background-repeat: no-repeat !important;
                background-size: 12px 12px !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                width: 100%;
            }
            html.dark select,
            html.dark .crm-select,
            html.dark select.crm-select {
                border: 1px solid var(--input-border) !important;
                background-color: var(--card-bg) !important;
                color: var(--text-main) !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 20 20' fill='%2394a3b8'%3e%3cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3e%3c/svg%3e") !important;
            }

            /* Select elements inside flex horizontal toolbars and filter bars: Medium Width */
            .flex select,
            .inline-flex select,
            form.flex select,
            .crm-filter-bar select,
            .crm-filter-select,
            .crm-cal-select,
            select.crm-filter-select {
                width: auto !important;
                min-width: 140px !important;
                max-width: 220px !important;
            }

            /* Full-width selects inside standard vertical forms */
            select.w-full,
            .form-group select,
            .grid > div > select,
            .crm-section select {
                width: 100% !important;
                max-width: 100% !important;
            }

            /* Universal Horizontal Filter Bar with Crisp 1px Border */
            .crm-filter-bar {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: wrap !important;
                align-items: center !important;
                gap: 8px !important;
                padding: 10px 14px !important;
                background-color: var(--card-bg);
                border: 1px solid var(--card-border) !important;
                border-radius: 10px;
            }
            html.dark .crm-filter-bar {
                background-color: var(--card-bg);
                border: 1px solid var(--card-border) !important;
            }

            /* Search Box with Medium Width and Clean Dimensions */
            .crm-search-box {
                position: relative !important;
                width: 280px !important;
                min-width: 240px !important;
                max-width: 360px !important;
                display: flex !important;
                align-items: center !important;
            }

            .crm-search-input,
            .crm-filter-bar input[type="text"],
            .crm-filter-bar input[type="search"],
            .relative > input.crm-cal-input {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                font-size: 13px !important;
                padding-left: 36px !important;
                padding-right: 12px !important;
                border: 1px solid var(--card-border) !important;
                border-radius: 8px !important;
                background-color: var(--card-bg) !important;
                color: var(--text-main) !important;
                outline: none !important;
                width: 100% !important;
            }
            html.dark .crm-search-input,
            html.dark .crm-filter-bar input[type="text"],
            html.dark .crm-filter-bar input[type="search"],
            html.dark .relative > input.crm-cal-input {
                background-color: #0b132b !important;
                border: 1px solid var(--input-border) !important;
                color: var(--text-main) !important;
            }

            /* Standardized Ergonomic Medium Button Size (38px medium height) */
            .crm-filter-btn,
            .crm-btn-primary,
            .crm-btn-secondary,
            .btn-primary,
            .btn-secondary {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                padding: 0 16px !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                border-radius: 8px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                white-space: nowrap !important;
                line-height: 1 !important;
                cursor: pointer;
                transition: all 0.15s ease !important;
            }

            .btn-primary,
            .crm-btn-primary,
            .crm-filter-btn {
                background-color: var(--crm-accent, #2563eb) !important;
                border: 1px solid var(--crm-accent, #2563eb) !important;
                color: #ffffff !important;
            }
            .btn-primary:hover,
            .crm-btn-primary:hover,
            .crm-filter-btn:hover {
                background-color: var(--crm-accent-hover, #1d4ed8) !important;
                border-color: var(--crm-accent-hover, #1d4ed8) !important;
            }
            .btn-primary *,
            .crm-btn-primary *,
            .crm-filter-btn * {
                color: #ffffff !important;
            }
            .btn-primary svg,
            .crm-btn-primary svg,
            .crm-filter-btn svg {
                stroke: #ffffff !important;
            }

            .btn-secondary,
            .crm-btn-secondary {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--button-border) !important;
                color: var(--text-main) !important;
            }
            .btn-secondary:hover,
            .crm-btn-secondary:hover {
                background-color: var(--table-header-bg) !important;
            }

            /* Dedicated Clean Helper & Module Navigation Buttons (Add New Customer, Add New Technician) */
            .btn-subtle,
            .btn-inline-add,
            a.btn-subtle,
            button.btn-subtle,
            a[class*="btn-subtle"],
            button[class*="btn-subtle"],
            a.btn-inline-add,
            button.btn-inline-add {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 14px !important;
                height: 32px !important;
                min-height: 32px !important;
                max-height: 32px !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                border-radius: 8px !important;
                background-color: var(--crm-accent, #2563eb) !important;
                border: 1px solid var(--crm-accent, #2563eb) !important;
                color: #ffffff !important;
                cursor: pointer !important;
                text-decoration: none !important;
                white-space: nowrap !important;
                transition: all 0.15s ease !important;
                box-shadow: 0 1px 2px rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25) !important;
            }
            .btn-subtle:hover,
            .btn-inline-add:hover,
            a.btn-subtle:hover,
            button.btn-subtle:hover,
            a[class*="btn-subtle"]:hover,
            button[class*="btn-subtle"]:hover {
                background-color: var(--crm-accent-hover, #1d4ed8) !important;
                border-color: var(--crm-accent-hover, #1d4ed8) !important;
                color: #ffffff !important;
            }
            .btn-subtle *,
            .btn-inline-add *,
            a.btn-subtle *,
            button.btn-subtle *,
            a[class*="btn-subtle"] *,
            button[class*="btn-subtle"] * {
                color: #ffffff !important;
                font-weight: 600 !important;
            }
            html.dark .btn-subtle,
            html.dark .btn-inline-add,
            html.dark a.btn-subtle,
            html.dark button.btn-subtle,
            html.dark a[class*="btn-subtle"],
            html.dark button[class*="btn-subtle"] {
                background-color: var(--crm-accent, #2563eb) !important;
                border: 1px solid color-mix(in srgb, var(--crm-accent, #2563eb) 80%, #fff) !important;
                color: #ffffff !important;
            }
            html.dark .btn-subtle:hover,
            html.dark .btn-inline-add:hover,
            html.dark a.btn-subtle:hover,
            html.dark button.btn-subtle:hover,
            html.dark a[class*="btn-subtle"]:hover,
            html.dark button[class*="btn-subtle"]:hover {
                background-color: var(--crm-accent-hover, #1d4ed8) !important;
                border-color: var(--crm-accent, #2563eb) !important;
                color: #ffffff !important;
            }
            html.dark .btn-subtle *,
            html.dark .btn-inline-add *,
            html.dark a.btn-subtle *,
            html.dark button.btn-subtle *,
            html.dark a[class*="btn-subtle"] *,
            html.dark button[class*="btn-subtle"] * {
                color: #ffffff !important;
                font-weight: 600 !important;
            }

            select.crm-select:invalid,
            select.crm-select option[value=""] {
                color: color-mix(in srgb, var(--crm-accent, #2563eb) 60%, #fff) !important;
            }

            html.dark select,
            html.dark .crm-select,
            html.dark select.crm-select {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--input-border) !important;
                color: var(--text-main) !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 20 20' fill='%2394a3b8'%3e%3cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3e%3c/svg%3e") !important;
            }

            select:focus,
            select:active,
            .crm-select:focus,
            .crm-select:active {
                border-color: var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
                outline: 2px solid var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
                outline-offset: -1px !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
            }

            /* Clean Single-Border Dropdown Select Styling (Matches Reference Design) */
            select.crm-select,
            .crm-select {
                padding-left: 14px !important;
                padding-right: 34px !important;
                letter-spacing: normal !important;
                text-indent: 0 !important;
            }

            /* Also style table search inputs to have the same crisp 1px dark border */
            .crm-search-bar,
            .search-input-box,
            input[type="search"]:not([class*="border-0"]):not([class*="bg-transparent"]),
            input[name="search"]:not([type="hidden"]):not([class*="border-0"]):not([class*="bg-transparent"]) {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                border: 1px solid var(--card-border) !important;
                border-radius: 6px !important;
                font-size: 13px !important;
                background-color: var(--card-bg) !important;
                color: var(--text-main) !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
                transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
            }

            html.dark .crm-search-bar,
            html.dark .search-input-box,
            html.dark input[type="search"]:not([class*="border-0"]):not([class*="bg-transparent"]),
            html.dark input[name="search"]:not([type="hidden"]):not([class*="border-0"]):not([class*="bg-transparent"]) {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--input-border) !important;
                color: var(--text-main) !important;
            }

            .crm-search-bar:focus,
            .search-input-box:focus,
            input[type="search"]:not([class*="border-0"]):not([class*="bg-transparent"]):focus,
            input[name="search"]:not([type="hidden"]):not([class*="border-0"]):not([class*="bg-transparent"]):focus {
                border-color: var(--crm-accent, #2563eb) !important;
                outline: none !important;
                box-shadow: 0 0 0 3px rgba(var(--crm-accent-rgb, 37, 99, 235), 0.28) !important;
            }

            /* Universal Badge, Label & Status Pill System with Crisp Single 1px Dark Border (Matching Image 4) */
            .badge,
            .status-badge,
            .tech-tag,
            .date-chip,
            span.badge,
            span[class*="rounded-full"][class*="border"],
            span[class*="rounded-md"][class*="border"],
            span[class*="rounded"][class*="border"],
            .cell-sub span,
            td > span[class*="rounded"],
            .leads-table td span[class*="rounded"],
            .jobs-table td span[class*="rounded"] {
                border-width: 1px !important;
                border-style: solid !important;
                border-color: var(--card-border) !important;
            }
            html.dark .badge,
            html.dark .status-badge,
            html.dark .tech-tag,
            html.dark .date-chip,
            html.dark span.badge,
            html.dark span[class*="rounded-full"][class*="border"],
            html.dark span[class*="rounded-md"][class*="border"],
            html.dark span[class*="rounded"][class*="border"],
            html.dark .cell-sub span,
            html.dark td > span[class*="rounded"],
            html.dark .leads-table td span[class*="rounded"],
            html.dark .jobs-table td span[class*="rounded"] {
                border-width: 1px !important;
                border-style: solid !important;
                border-color: var(--input-border) !important;
            }

            /* Omnisearch Result Badge: Compact, Crisp, Proportionate */
            .crm-search-badge {
                font-size: 9.5px !important;
                font-weight: 600 !important;
                line-height: 12px !important;
                letter-spacing: 0.04em !important;
                padding: 1.5px 6.5px !important;
                border-radius: 4px !important;
                border-width: 1px !important;
                border-style: solid !important;
                text-transform: uppercase !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                white-space: nowrap !important;
                flex-shrink: 0 !important;
            }

            /* Universal Clean Buttons: Remove decorative icons inside text buttons across all pages for all users while preserving action/icon buttons */
            button:not(.btn-icon):not(.btn-close):not(.allow-brand-icon):not(#header-theme-toggle-btn):not(#header-notification-btn):not(#sidebar-logout-btn):not(.sidebar-logout-btn):not([aria-label*="log out" i]):not([aria-label*="logout" i]):not(#header-sidebar-toggle-btn):not([aria-label*="sidebar" i]):not([aria-label*="navigation" i]):not([aria-label*="close" i]):not([aria-label*="Close"]):not([aria-label*="clear" i]):not([aria-label*="Clear"]):not(.crm-toast__close):not(.pwa-close):not([class*="close"]):not([class*="clear"]):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.ld-view-switcher):not(.action-btn):not(.btn-action):not(.ld-page-arrow):not(.ld-row-actions *):not([aria-label*="action" i]):not([title*="action" i]):not([aria-label*="option" i]):not([title*="option" i]):not([aria-label*="scroll" i]):not([title*="scroll" i]):not([aria-label*="column" i]):not([title*="column" i]):not([aria-label*="view" i]):not([title*="view" i]):not([aria-label*="more" i]):not([title*="more" i]):not([aria-label*="sort" i]):not([title*="sort" i]):not([aria-label*="filter" i]):not([title*="filter" i]):not([aria-label*="edit" i]):not([title*="edit" i]):not([aria-label*="delete" i]):not([title*="delete" i]) > svg,
            a[class*="btn"]:not(.btn-icon):not(.allow-brand-icon):not([aria-label*="close" i]):not([aria-label*="clear" i]):not([class*="close"]):not([class*="clear"]):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.ld-view-switcher):not(.action-btn):not(.btn-action):not(.ld-page-arrow):not([aria-label*="action" i]):not([title*="action" i]):not([aria-label*="option" i]):not([title*="option" i]):not([aria-label*="scroll" i]):not([title*="scroll" i]):not([aria-label*="column" i]):not([title*="column" i]):not([aria-label*="view" i]):not([title*="view" i]):not([aria-label*="more" i]):not([title*="more" i]):not([aria-label*="sort" i]):not([title*="sort" i]):not([aria-label*="filter" i]):not([title*="filter" i]):not([aria-label*="edit" i]):not([title*="edit" i]):not([aria-label*="delete" i]):not([title*="delete" i]) > svg,
            .btn-primary:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg,
            .btn-secondary:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]):not([aria-label*="more" i]):not([title*="more" i]) svg,
            .btn-danger:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]):not([aria-label*="delete" i]):not([title*="delete" i]) svg,
            .btn-blue:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg,
            .btn-white:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg,
            .btn-amber:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg,
            .crm-btn-primary:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg,
            .crm-btn-secondary:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg,
            .btn-submit-primary:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg,
            .btn-cancel:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not(.ld-row-actions *):not([aria-label*="action" i]):not([title*="action" i]):not([aria-label*="delete" i]):not([title*="delete" i]) svg,
            .btn-head-secondary:not(.btn-icon):not(.ld-outline):not(.ld-icon-btn):not(.ld-tool):not(.action-btn):not([aria-label*="action" i]):not([title*="action" i]) svg {
                display: none !important;
            }

            /* Explicit override to guarantee action buttons, scroll controls, clear, close, theme, logout, and navigation icons always display */
            button.btn-icon > svg,
            a.btn-icon > svg,
            button.btn-close > svg,
            button#sidebar-logout-btn > svg,
            button.sidebar-logout-btn > svg,
            button[aria-label*="log out" i] > svg,
            button[aria-label*="logout" i] > svg,
            button#header-sidebar-toggle-btn > svg,
            button[aria-label*="sidebar" i] > svg,
            button[aria-label*="Toggle navigation" i] > svg,
            button[aria-label*="Clear" i] > svg,
            button[aria-label*="Close" i] > svg,
            .crm-search-clear-btn svg,
            .ld-outline svg,
            .ld-icon-btn svg,
            .ld-tool svg,
            .ld-view-switcher svg,
            .ld-row-actions svg,
            .ld-page-arrow svg,
            .action-btn svg,
            .btn-action svg,
            button[aria-label*="action" i] svg,
            button[aria-label*="actions" i] svg,
            button[title*="action" i] svg,
            button[title*="actions" i] svg,
            button[aria-label*="option" i] svg,
            button[aria-label*="options" i] svg,
            button[title*="option" i] svg,
            button[title*="options" i] svg,
            button[aria-label*="scroll" i] svg,
            button[title*="scroll" i] svg,
            button[aria-label*="column" i] svg,
            button[aria-label*="columns" i] svg,
            button[title*="column" i] svg,
            button[title*="columns" i] svg,
            button[aria-label*="view" i] svg,
            button[aria-label*="views" i] svg,
            button[title*="view" i] svg,
            button[title*="views" i] svg,
            button[aria-label*="more" i] svg,
            button[title*="more" i] svg,
            button[aria-label*="sort" i] svg,
            button[title*="sort" i] svg,
            button[aria-label*="filter" i] svg,
            button[title*="filter" i] svg,
            button[aria-label*="edit" i] svg,
            button[title*="edit" i] svg,
            button[aria-label*="delete" i] svg,
            button[title*="delete" i] svg,
            a[aria-label*="action" i] svg,
            a[aria-label*="actions" i] svg,
            a[title*="action" i] svg,
            a[title*="actions" i] svg,
            a[aria-label*="edit" i] svg,
            a[title*="edit" i] svg,
            a[aria-label*="delete" i] svg,
            a[title*="delete" i] svg {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .ld-outline[aria-label*="action" i],
            .ld-outline[title*="action" i],
            .ld-outline[aria-label*="option" i],
            .ld-outline[title*="option" i] {
                width: 38px !important;
                min-width: 38px !important;
                padding: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .ld-outline[aria-label*="action" i] svg,
            .ld-outline[title*="action" i] svg,
            .ld-outline[aria-label*="option" i] svg,
            .ld-outline[title*="option" i] svg {
                width: 16px !important;
                height: 16px !important;
                display: inline-block !important;
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

                // 2. Navigation: Ctrl+Z (Go back) & Ctrl+X (Go forward) when not typing in form inputs
                if (!isInput && (e.ctrlKey || e.metaKey)) {
                    if (e.key === 'z' || e.key === 'Z') {
                        e.preventDefault();
                        window.history.back();
                        return;
                    }
                    if (e.key === 'x' || e.key === 'X') {
                        e.preventDefault();
                        window.history.forward();
                        return;
                    }
                }

                // Ctrl+K / Cmd+K (search) is handled once, by the header component (globalHeader)

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
                        window.location.href = '{{ auth()->user()->homeUrl() }}';
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

            // ═══════════════════════════════════════════════════════════
            // UNIVERSAL HARDWARE BARCODE SCANNER MACHINE GUN LISTENER
            // Detects rapid keystroke bursts (< 55ms) from USB/Bluetooth Scanner Guns
            // ═══════════════════════════════════════════════════════════
            (function() {
                let gunBuffer = '';
                let lastKeyTime = 0;
                const MAX_BURST_DELTA = 55; // ms between keys from hardware scanner gun

                window.addEventListener('keydown', function(event) {
                    // Ignore hotkeys with modifiers
                    if (event.ctrlKey || event.altKey || event.metaKey) return;

                    const now = Date.now();
                    const delta = now - lastKeyTime;
                    lastKeyTime = now;

                    // Barcode scanner guns terminate scan with Enter key
                    if (event.key === 'Enter') {
                        if (gunBuffer.length >= 3 && delta < 120) {
                            // Hardware scanner gun pull detected!
                            event.preventDefault();
                            event.stopPropagation();

                            const rawCode = gunBuffer;
                            gunBuffer = '';

                            const clean = window.cleanBarcode ? window.cleanBarcode(rawCode) : rawCode.trim();
                            if (!clean) return;

                            // 1. Audio Confirmation Beep
                            try {
                                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.type = 'sine';
                                osc.frequency.setValueAtTime(1046.5, ctx.currentTime);
                                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.start();
                                osc.stop(ctx.currentTime + 0.12);
                            } catch (e) {}

                            // 2. Custom event dispatch
                            window.dispatchEvent(new CustomEvent('hardware-gun-scan', {
                                detail: { code: clean, raw: rawCode }
                            }));

                            // 3. If Barcode Scanner modal is open, forward to modal
                            const openModal = document.querySelector('.crm-modal:not([style*="display: none"]), [id$="-barcode-modal"]:not([style*="display: none"])');
                            if (openModal) {
                                const manualInp = openModal.querySelector('input[id$="-manual-input"]');
                                if (manualInp) {
                                    manualInp.value = clean;
                                    const modalId = openModal.id;
                                    if (typeof window.handleBarcodeDetected === 'function') {
                                        window.handleBarcodeDetected(modalId, clean);
                                        if (typeof window.closeBarcodeScanner === 'function') {
                                            window.closeBarcodeScanner(modalId);
                                        }
                                        return;
                                    }
                                }
                            }

                            // 4. Populate Hardware Serial Number field directly on page
                            const serialInput = document.getElementById('serial_number_input') 
                                             || document.getElementById('rma_serial_input') 
                                             || document.querySelector('input[name="serial_number"]')
                                             || document.querySelector('input[name="faulty_serial_number"]');

                            if (serialInput) {
                                serialInput.value = clean;
                                serialInput.dispatchEvent(new Event('input', { bubbles: true }));
                                serialInput.dispatchEvent(new Event('change', { bubbles: true }));
                                serialInput.classList.add('ring-2', 'ring-emerald-500');
                                setTimeout(() => serialInput.classList.remove('ring-2', 'ring-emerald-500'), 1500);

                                if (window.onEquipmentScanSuccess && typeof window.onEquipmentScanSuccess === 'function') {
                                    const parsed = window.parseDeviceBarcode ? window.parseDeviceBarcode(clean) : { serial: clean, mac: '', model: '' };
                                    window.onEquipmentScanSuccess(clean, parsed, null, 'serial_number_input');
                                } else if (window.onEquipmentEditScanSuccess && typeof window.onEquipmentEditScanSuccess === 'function') {
                                    const parsed = window.parseDeviceBarcode ? window.parseDeviceBarcode(clean) : { serial: clean, mac: '', model: '' };
                                    window.onEquipmentEditScanSuccess(clean, parsed, null, 'serial_number_input');
                                }

                                if (typeof window.crmToast === 'function') {
                                    window.crmToast(`Hardware S/N Added: ${clean}`, 'success');
                                }
                                return;
                            }

                            // 5. If an active form input was focused, populate it
                            const active = document.activeElement;
                            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA') && !active.readOnly) {
                                active.value = clean;
                                active.dispatchEvent(new Event('input', { bubbles: true }));
                                active.dispatchEvent(new Event('change', { bubbles: true }));
                                active.classList.add('ring-2', 'ring-emerald-500');
                                setTimeout(() => active.classList.remove('ring-2', 'ring-emerald-500'), 1200);
                            }
                        }
                        gunBuffer = '';
                        return;
                    }

                    // Buffer characters received at hardware scanner speed
                    if (event.key && event.key.length === 1) {
                        if (delta > MAX_BURST_DELTA) {
                            gunBuffer = event.key;
                        } else {
                            gunBuffer += event.key;
                        }
                    }
                }, true);
            })();
        </script>
        @if(request()->routeIs('calendar.*'))
        <style>
            body, body * {
                font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }
            body {
                font-size: 14px;
            }
        </style>
        @endif
    </head>
    <body class="h-full overflow-x-hidden font-sans antialiased text-slate-800 dark:text-slate-100 bg-[var(--page-bg)] selection:bg-blue-600 selection:text-white" 
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
          @shortcuts-close.window="shortcutsModalOpen = false">
            <a href="#main-content" class="crm-skip-link text-white">Skip to main content</a>
        
            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- SECUREVISION UNIFIED ENTERPRISE SAAS CRM SUITE (ALL ROLES)        --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="crm-app-container min-h-screen lg:h-screen lg:overflow-hidden flex bg-[var(--page-bg)] text-[var(--text-main)] transition-colors duration-200">
                
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

                {{-- Sidebar Container: Fixed on mobile, sticky on desktop, 220px wide, 1px solid border --}}
                <div :class="{
                        'translate-x-0': sidebarOpen,
                        '-translate-x-full lg:translate-x-0': !sidebarOpen,
                        'lg:w-20': sidebarCollapsed,
                        'lg:w-[220px]': !sidebarCollapsed
                     }"
                     class="crm-sidebar-container fixed inset-y-0 left-0 z-50 lg:sticky lg:top-0 lg:h-screen lg:z-30 w-[220px] shrink-0 transition-[width,transform] duration-200 ease-in-out flex flex-col bg-[var(--card-bg)] text-[var(--text-main)] border-r border-[var(--card-border)] overflow-hidden">
                    @include('layouts.navigation')
                </div>

                {{-- Main Application Wrapper --}}
                <div class="flex-1 flex flex-col min-w-0 min-h-screen lg:min-h-0 lg:h-full lg:overflow-hidden bg-[var(--page-bg)] text-[var(--text-main)] transition-colors duration-200">
                    
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
                                },
                                getBadgeClass(badge) {
                                    const b = (badge || '').toLowerCase();
                                    if (b.includes('quote') || b.includes('quoted')) {
                                        return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800';
                                    }
                                    if (b.includes('project')) {
                                        return 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800';
                                    }
                                    if (b.includes('survey')) {
                                        return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800';
                                    }
                                    if (b.includes('product')) {
                                        return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800';
                                    }
                                    if (b.includes('lead') || b.includes('new') || b.includes('contacted')) {
                                        return 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800';
                                    }
                                    if (b.includes('won') || b.includes('paid')) {
                                        return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800';
                                    }
                                    if (b.includes('lost') || b.includes('cancelled') || b.includes('overdue')) {
                                        return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800';
                                    }
                                    if (b.includes('job') || b.includes('task')) {
                                        return 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950/60 dark:text-violet-300 dark:border-violet-800';
                                    }
                                    if (b.includes('asset') || b.includes('camera') || b.includes('equipment')) {
                                        return 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-800';
                                    }
                                    return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
                                }
                            };
                        }
                    </script>

                    {{-- Fixed Top Bar: 56px high --}}
                    <header class="crm-topbar flex h-[56px] min-h-[56px] max-h-[56px] shrink-0 bg-[var(--card-bg)] text-[var(--text-main)] border-b border-[var(--card-border)] px-5 items-center justify-between sticky top-0 z-30"
                            x-data="globalHeader()"
                            @theme-mode-changed.window="isDarkMode = $event.detail.isDark"
                            @theme-changed.window="if ($event.detail && typeof $event.detail.isDark === 'boolean') isDarkMode = $event.detail.isDark"
                            @sidebar-collapsed-changed.window="sidebarCollapsed = $event.detail.collapsed"
                            @keydown.escape.window="open = false; mobileSearchOpen = false"
                            @keydown.window.prevent.ctrl.k="handleShortcut(window.innerWidth < 768)"
                            @keydown.window.prevent.cmd.k="handleShortcut(window.innerWidth < 768)">
                        
                        {{-- 1. Page Name (Left): min-w-0 so a long (e.g. Tamil) title truncates instead of pushing the account menu off a phone screen --}}
                        <div class="flex items-center gap-2 min-w-0">
                            {{-- Mobile Hamburger Toggle --}}
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    type="button"
                                    class="btn-icon lg:hidden inline-flex items-center justify-center p-1.5 rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0"
                                    aria-label="Toggle navigation menu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>

                            {{-- Desktop Sidebar Collapse Toggle (same action as Ctrl+B) --}}
                            <button @click="window.crmSidebar.toggle()"
                                    type="button"
                                    id="header-sidebar-toggle-btn"
                                    class="btn-icon hidden lg:inline-flex items-center justify-center w-8 h-8 rounded-[6px] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer shrink-0 border border-slate-200/80 dark:border-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600 bg-white/50 dark:bg-slate-800/50"
                                    :title="sidebarCollapsed ? 'Expand sidebar (Ctrl+B)' : 'Collapse sidebar (Ctrl+B)'"
                                    :aria-expanded="(!sidebarCollapsed).toString()"
                                    aria-label="Toggle sidebar">
                                <svg class="w-4 h-4 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect width="18" height="18" x="3" y="3" rx="3" stroke-width="1.8"/>
                                    <line stroke-linecap="round" x1="9" x2="9" y1="3" y2="21" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="sidebarCollapsed ? 'm13 9 3 3-3 3' : 'm16 9-3 3 3 3'"/>
                                </svg>
                            </button>

                            <span class="crm-page-title text-sm font-bold text-[var(--text-main)] truncate">{{ __($crmPageTitle) }}</span>
                        </div>

                        @if(auth()->user()->isInternal())
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
                                           aria-label="Search leads, jobs, quotes and equipment"
                                           x-model="query"
                                           @input.debounce.250ms="search()"
                                           @focus="if(query.length >= 2) open = true"
                                           @keydown.arrow-down.prevent="navigate(1)"
                                           @keydown.arrow-up.prevent="navigate(-1)"
                                           @keydown.enter.prevent="selectActive()"
                                           placeholder="Search leads, jobs, quotes, equipment..."
                                           style="padding-left: 2.5rem !important; height: 38px !important; border-radius: 6px !important;"
                                           class="crm-search-input w-full text-xs bg-[var(--card-bg)] border border-[var(--input-border)] rounded text-[var(--text-main)] placeholder-[var(--text-muted)] focus:outline-none">
                                    
                                    {{-- Clear button or loading spinner --}}
                                    <div class="absolute right-2.5 flex items-center">
                                        <template x-if="loading">
                                            <svg class="animate-spin h-3.5 w-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!loading && query.length > 0">
                                            <x-close-button size="sm" label="Clear search" class="crm-search-clear-btn" @click="clear()" />
                                        </template>
                                    </div>
                                </div>

                                {{-- Search Dropdown Results --}}
                                <div x-show="open && query.trim().length >= 2 && !mobileSearchOpen"
                                     class="absolute left-0 right-0 mt-1 w-full max-h-96 overflow-y-auto bg-[var(--card-bg)] border border-[var(--card-border)] rounded-lg py-1 z-50 divide-y divide-[var(--card-border)]"
                                     style="display: none;">
                                    <template x-if="results.length > 0">
                                        <div class="py-1">
                                            <template x-for="(item, index) in results" :key="index">
                                                <a :href="item.url" 
                                                   :class="selectedIndex === index ? 'bg-slate-100 dark:bg-slate-800 text-[var(--text-main)]' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                                                   class="flex items-start gap-3 px-3 py-2 transition text-left group block">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <p class="text-xs font-bold text-[var(--text-main)] truncate" x-text="item.title"></p>
                                                            <span class="crm-search-badge shrink-0"
                                                                  :class="getBadgeClass(item.badge)"
                                                                  style="font-size: 9.5px !important; line-height: 12px !important; font-weight: 600 !important; letter-spacing: 0.04em !important; padding: 1.5px 6.5px !important; border-radius: 4px !important; text-transform: uppercase !important; display: inline-flex !important; align-items: center !important;"
                                                                  x-text="item.badge"></span>
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
                        @else
                            <div class="hidden md:block flex-1"></div>
                        @endif

                        @php
                            $headerNotifs = \App\Services\HeaderNotificationService::getNotificationsForUser(Auth::user());
                        @endphp

                        {{-- 3. Topbar Controls: Search, Theme Toggle, Notification Bell & Pill Profile (Right) --}}
                        <div class="flex items-center gap-2.5 shrink-0">
                            @if(auth()->user()->isInternal())
                            {{-- Mobile search trigger icon --}}
                            <button @click="mobileSearchOpen = true; $nextTick(() => { $refs.mobileSearchInput.focus(); if (query.trim().length >= 2) open = true; })" 
                                    type="button"
                                    class="md:hidden p-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition shrink-0 cursor-pointer"
                                    title="Search">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                            @endif

                            {{-- Universal 1-Click Dark/Light Mode Button on Every Page for All Users --}}
                            <button @click="toggleThemeMode()" 
                                    type="button" 
                                    id="header-theme-toggle-btn"
                                    class="btn-icon relative inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition cursor-pointer shrink-0 shadow-2xs" 
                                    :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'" 
                                    aria-label="Toggle dark/light mode">
                                {{-- Sun Icon (shown when dark mode is active) --}}
                                <svg x-show="isDarkMode" x-cloak class="w-4 h-4 text-amber-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="4" fill="currentColor" fill-opacity="0.25"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                                </svg>
                                {{-- Moon Icon (shown when light mode is active) --}}
                                <svg x-show="!isDarkMode" class="w-4 h-4 text-slate-700 dark:text-slate-200 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="currentColor" fill-opacity="0.18"/>
                                </svg>
                            </button>

                            {{-- Compact Dismissible PWA Install Header Pill (non-overlapping) --}}
                            <div x-data="{
                                pwa: window.crmPwa || { canInstall: false, isStandalone: false, isDismissed: localStorage.getItem('pwa_install_dismissed') === 'true' }
                            }"
                                 x-init="window.addEventListener('crm:pwa-state', e => { pwa = e.detail; })"
                                 x-show="pwa.canInstall && !pwa.isDismissed && !pwa.isStandalone"
                                 x-cloak
                                 class="hidden sm:inline-flex items-center rounded-xl border border-blue-500/30 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 shadow-2xs overflow-hidden shrink-0 h-9">
                                <button type="button"
                                        @click="window.crmPwa?.install()"
                                        class="inline-flex items-center gap-1.5 px-2.5 h-full text-xs font-semibold hover:bg-blue-100/80 dark:hover:bg-blue-900/60 transition cursor-pointer"
                                        title="{{ __('Install Precision IT Field App') }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V3"/>
                                    </svg>
                                    <span>{{ __('Install App') }}</span>
                                </button>
                                <button type="button"
                                        @click="window.crmPwa?.dismiss()"
                                        class="inline-flex items-center justify-center w-6 h-full text-blue-500/80 hover:text-blue-700 dark:hover:text-blue-200 hover:bg-blue-100/80 dark:hover:bg-blue-900/60 border-l border-blue-500/20 transition cursor-pointer"
                                        aria-label="{{ __('Dismiss install prompt') }}"
                                        title="{{ __('Dismiss') }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Modern Notification Bell & Popover Module --}}
                            <div x-data="{
                                notifOpen: false,
                                unreadCount: {{ $headerNotifs['unread_count'] }},
                                async markAllRead() {
                                    const previous = this.unreadCount;
                                    this.unreadCount = 0;
                                    try {
                                        const res = await fetch('{{ route('notifications.read-all') }}', {
                                            method: 'POST',
                                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                        });
                                        if (!res.ok) throw new Error(res.status);
                                        this.unreadCount = (await res.json()).unread_count ?? 0;
                                    } catch (e) {
                                        this.unreadCount = previous;
                                        window.crmAlert?.('Could not mark notifications as read. Please try again.', 'error');
                                    }
                                }
                            }" class="relative"
                                 x-init="$watch('notifOpen', open => open && $nextTick(() => crmFocusFirst($refs.notifPanel)))"
                                 @keydown.escape="if (notifOpen) { notifOpen = false; $refs.notifBtn.focus(); }">
                                <button @click="notifOpen = !notifOpen"
                                        type="button"
                                        id="header-notification-btn"
                                        x-ref="notifBtn"
                                        aria-haspopup="true"
                                        aria-controls="crm-notif-panel"
                                        :aria-expanded="notifOpen.toString()"
                                        class="btn-icon relative inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition cursor-pointer shrink-0 shadow-2xs" 
                                        title="Notifications & System Alerts" 
                                        aria-label="View notifications">
                                    <svg class="w-4 h-4 text-slate-700 dark:text-slate-200 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount" 
                                          class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-rose-500 rounded-full leading-none shadow-xs ring-2 ring-white dark:ring-slate-900 pointer-events-none">
                                    </span>
                                </button>

                                {{-- Notification Popover Dropdown --}}
                                <div x-show="notifOpen"
                                     x-ref="notifPanel"
                                     id="crm-notif-panel"
                                     role="region"
                                     aria-label="Notifications"
                                     @keydown.tab="crmTrapTab($event, $refs.notifPanel)"
                                     @click.away="notifOpen = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="fixed sm:absolute inset-x-3 sm:inset-x-auto sm:right-0 top-14 sm:top-auto sm:mt-2 w-auto sm:w-96 max-w-[calc(100vw-1.5rem)] bg-[var(--card-bg)] rounded-2xl border border-[var(--card-border)] shadow-2xl py-0 z-50 overflow-hidden text-xs text-slate-700 dark:text-slate-200"
                                     style="display: none;">
                                    
                                    {{-- Popover Header --}}
                                    <div class="px-4 py-3 border-b border-[var(--card-border)] bg-slate-50/80 dark:bg-slate-900/80 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white">Notifications</span>
                                            <span x-show="unreadCount > 0" class="px-1.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400" x-text="unreadCount + ' new'"></span>
                                        </div>
                                        <button type="button" @click="markAllRead()" class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                            Mark all as read
                                        </button>
                                    </div>

                                    {{-- Items List --}}
                                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/80">
                                        @forelse($headerNotifs['items'] as $item)
                                            @php $rowTag = $item['url'] ? 'a' : 'div'; @endphp
                                            <{{ $rowTag }} @if($item['url']) href="{{ $item['url'] }}" @endif
                                               @click="notifOpen = false"
                                               class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition group block relative">
                                                
                                                {{-- Icon badge --}}
                                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center 
                                                    @if($item['icon'] === 'ticket') bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900
                                                    @elseif($item['icon'] === 'inventory') bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900
                                                    @elseif($item['icon'] === 'expense' || $item['icon'] === 'receipt') bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900
                                                    @elseif($item['icon'] === 'job') bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900
                                                    @elseif($item['icon'] === 'survey') bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-900
                                                    @elseif($item['icon'] === 'quotation') bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-900
                                                    @elseif($item['icon'] === 'attendance') bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-900
                                                    @elseif($item['icon'] === 'log') bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900
                                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700
                                                    @endif">
                                                    @if($item['icon'] === 'ticket')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                    @elseif($item['icon'] === 'inventory')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                    @elseif($item['icon'] === 'expense' || $item['icon'] === 'receipt')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm4.5 6h.008v.008h-.008V15z"/></svg>
                                                    @elseif($item['icon'] === 'survey')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    @elseif($item['icon'] === 'quotation')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                    @elseif($item['icon'] === 'job')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    @elseif($item['icon'] === 'attendance')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 shrink-0">{{ $item['time'] }}</span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $item['description'] }}</p>
                                                </div>

                                                {{-- Quick Direct-Action Chevron --}}
                                                @if($item['url'])
                                                    <span class="self-center text-slate-300 dark:text-slate-600 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-0.5 transition-all text-xs font-bold shrink-0">
                                                        &rarr;
                                                    </span>
                                                @endif
                                            </{{ $rowTag }}>
                                        @empty
                                            <div class="p-6 text-center text-slate-400">
                                                <p class="text-xs">{{ __('No pending notifications') }}</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Footer Action Link --}}
                                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border-t border-[var(--card-border)] text-center">
                                        @if(Auth::user()->isAdmin())
                                            <a href="{{ route('alerts.index') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View Automated Alerts Engine →
                                            </a>
                                        @elseif(Auth::user()->isTechnician())
                                            <a href="{{ route('technician.dashboard') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ __('View All Assigned Work Orders') }} →
                                            </a>
                                        @elseif(Auth::user()->isOffice())
                                            <a href="{{ route('dashboard') }}" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View Operations Dashboard →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Pill-Shaped User Profile Capsule & Dropdown Menu --}}
                            <div x-data="{ userMenuOpen: false }" class="relative"
                                 x-init="$watch('userMenuOpen', open => open && $nextTick(() => crmFocusFirst($refs.userPanel)))"
                                 @keydown.escape="if (userMenuOpen) { userMenuOpen = false; $refs.userBtn.focus(); }">
                                <button @click="userMenuOpen = !userMenuOpen"
                                        type="button"
                                        id="header-user-profile-pill"
                                        x-ref="userBtn"
                                        aria-haspopup="true"
                                        aria-controls="crm-user-panel"
                                        :aria-expanded="userMenuOpen.toString()"
                                        aria-label="{{ __('Account menu for :name', ['name' => Auth::user()->name]) }}"
                                        class="flex items-center gap-2 pl-1.5 pr-2.5 h-9 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition cursor-pointer select-none shadow-2xs">
                                    @if(Auth::user()->avatar_url)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                        <div class="w-7 h-7 rounded-full text-white items-center justify-center font-bold text-xs shrink-0" style="background-color: var(--crm-accent, #2563eb); display: none;">
                                            {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="w-7 h-7 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0" style="background-color: var(--crm-accent, #2563eb);">
                                            {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="text-left hidden sm:block min-w-0 pr-0.5">
                                        <span class="block text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                        <span class="block text-[11px] text-blue-600 dark:text-blue-400 font-semibold leading-tight truncate">{{ __(Auth::user()->role_title) }}</span>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0 transition-transform duration-150" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- User dropdown menu (contains settings, theme switcher, logout) --}}
                                <div x-show="userMenuOpen"
                                     x-ref="userPanel"
                                     id="crm-user-panel"
                                     role="region"
                                     aria-label="{{ __('Account menu') }}"
                                     @keydown.tab="crmTrapTab($event, $refs.userPanel)"
                                     @click.away="userMenuOpen = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-72 sm:w-80 max-w-[calc(100vw-20px)] bg-[var(--card-bg)] rounded-2xl border border-[var(--card-border)] shadow-2xl py-1 z-50 text-xs font-medium text-slate-700 dark:text-slate-200 overflow-hidden"
                                     style="display: none;">
                                    
                                    {{-- User identity card in menu --}}
                                    <div class="px-4 py-3.5 border-b border-[var(--card-border)] bg-slate-50/80 dark:bg-slate-900/80 flex items-center gap-3">
                                        @if(Auth::user()->avatar_url)
                                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover shrink-0 ring-2 ring-white dark:ring-slate-800 shadow-xs" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                            <div class="w-10 h-10 rounded-full text-white items-center justify-center font-bold text-sm shrink-0 shadow-xs" style="background-color: var(--crm-accent, #2563eb); display: none;">
                                                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-full text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs" style="background-color: var(--crm-accent, #2563eb);">
                                                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-sm text-slate-900 dark:text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate leading-tight mt-0.5">{{ Auth::user()->email }}</p>
                                            <span class="inline-flex items-center gap-1.5 mt-1.5 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                                                <span>{{ __(Auth::user()->role_title) }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-1.5 space-y-0.5">
                                        <a href="{{ route('profile.edit') }}" class="crm-menu-item plain-link">
                                            <div class="crm-menu-icon-box text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800">
                                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                            </div>
                                            <span class="crm-menu-label">{{ __('Profile Settings & Photo') }}</span>
                                            <svg class="w-3.5 h-3.5 crm-menu-end-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>

                                        @if(Auth::user()->isAdmin())
                                            <a href="{{ route('admin.users.index') }}" class="crm-menu-item plain-link">
                                                <div class="crm-menu-icon-box text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60">
                                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                </div>
                                                <span class="crm-menu-label">{{ __('Manage User Accounts') }}</span>
                                                <svg class="w-3.5 h-3.5 crm-menu-end-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        @endif

                                        <div x-data="{ pwa: window.crmPwa || { canInstall: false, isStandalone: false } }"
                                             x-init="window.addEventListener('crm:pwa-state', e => { pwa = e.detail; })"
                                             x-show="pwa.canInstall && !pwa.isStandalone"
                                             x-cloak>
                                            <button type="button" @click="userMenuOpen = false; window.crmPwa?.install()" class="crm-menu-item">
                                                <div class="crm-menu-icon-box text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60">
                                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                </div>
                                                <span class="crm-menu-label">{{ __('Install Field App') }}</span>
                                                <span class="crm-menu-badge bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300">PWA</span>
                                            </button>
                                        </div>

                                        <button type="button" @click="toggleThemeMode()" class="crm-menu-item">
                                            <div class="crm-menu-icon-box text-amber-500 bg-amber-50 dark:bg-amber-950/60">
                                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                                            </div>
                                            <span class="crm-menu-label">{{ __('Theme Mode') }}</span>
                                            <span class="crm-menu-badge bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700" x-text="isDarkMode ? {{ Js::from(__('Night')) }} : {{ Js::from(__('Day')) }}"></span>
                                        </button>

                                        <button type="button" @click="userMenuOpen = false; window.crmOpenShortcuts()" class="hidden lg:flex crm-menu-item">
                                            <div class="crm-menu-icon-box text-slate-500 bg-slate-100 dark:bg-slate-800">
                                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 9h.01M10 9h.01M14 9h.01M18 9h.01M6 13h.01M18 13h.01M9 15h6"/></svg>
                                            </div>
                                            <span class="crm-menu-label">{{ __('Keyboard Shortcuts') }}</span>
                                            <kbd class="crm-kbd">?</kbd>
                                        </button>
                                    </div>

                                    <div class="p-1.5 border-t border-[var(--card-border)] bg-slate-50/40 dark:bg-slate-900/40">
                                        <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                            @csrf
                                            <button type="submit" class="crm-menu-item crm-menu-logout text-rose-600 dark:text-rose-400">
                                                <div class="crm-menu-icon-box text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60">
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                                </div>
                                                <span class="crm-menu-label font-bold text-rose-600 dark:text-rose-400">{{ __('Log Out') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->isInternal())
                        {{-- Mobile Omnisearch Full-Width Overlay --}}
                        <div x-show="mobileSearchOpen"
                             x-cloak
                             class="absolute inset-x-0 top-0 bg-[var(--card-bg)] z-50 px-3 py-2 border-b border-[var(--card-border)] md:hidden shadow-lg"
                             style="display: none;">
                            
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1 flex items-center">
                                    <input type="text" 
                                           x-ref="mobileSearchInput"
                                           aria-label="Search leads, projects and products"
                                           x-model="query"
                                           @input.debounce.250ms="search()"
                                           @focus="if(query.length >= 2) open = true"
                                           @keydown.arrow-down.prevent="navigate(1)"
                                           @keydown.arrow-up.prevent="navigate(-1)"
                                           @keydown.enter.prevent="selectActive()"
                                           placeholder="Search leads, projects, products..."
                                           style="height: 38px !important; border-radius: 6px !important;"
                                           class="w-full px-3 text-xs bg-white dark:bg-slate-900 border border-[var(--card-border)] rounded text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none">
                                </div>

                                <button @click="mobileSearchOpen = false; open = false;" 
                                        type="button" 
                                        class="px-2 py-1 rounded text-xs font-semibold text-slate-600 dark:text-slate-300 shrink-0 cursor-pointer">
                                    Cancel
                                </button>
                            </div>

                            {{-- Mobile Dropdown Results --}}
                            <div x-show="open && query.trim().length >= 2"
                                 class="mt-2 max-h-80 overflow-y-auto bg-[var(--card-bg)] border border-[var(--card-border)] rounded-lg py-1 divide-y divide-[var(--card-border)] shadow-xl">
                                <template x-if="results.length > 0">
                                    <div class="py-1">
                                        <template x-for="(item, index) in results" :key="'mob-' + index">
                                            <a :href="item.url" 
                                               class="flex items-start gap-2.5 px-3 py-2 transition text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 block">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-1.5">
                                                        <p class="text-xs font-bold text-[var(--text-main)] truncate" x-text="item.title"></p>
                                                        <span class="crm-search-badge shrink-0"
                                                              :class="getBadgeClass(item.badge)"
                                                              style="font-size: 9.5px !important; line-height: 12px !important; font-weight: 600 !important; letter-spacing: 0.04em !important; padding: 1.5px 6.5px !important; border-radius: 4px !important; text-transform: uppercase !important; display: inline-flex !important; align-items: center !important;"
                                                              x-text="item.badge"></span>
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
                        @endif
                    </header>

                    {{-- Main Content Area: Responsive padding p-3 on mobile, p-5 on desktop --}}
                    <main id="main-content" tabindex="-1" class="flex-1 outline-none overflow-y-auto overflow-x-hidden bg-[var(--page-bg)] p-3 sm:p-5 transition-colors duration-200 pb-28 lg:pb-8">
                        <div class="crm-content max-w-[1200px] mx-auto w-full">
                            {{-- Page Heading (One h2 at top of content area, with main action button on right) --}}
                            @isset($header)
                                <div class="crm-page-header mb-[20px]">
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
            @unless(request()->routeIs('technician.*') || (auth()->check() && auth()->user()->isTechnician()))
            <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-[#0f172a]/95 backdrop-blur-md border-t border-slate-200/90 dark:border-slate-800 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-3 py-1.5 transition-colors">
                <div class="max-w-md mx-auto flex items-center justify-between">
                    @if(auth()->user()->role === 'technician')
                        {{-- Field App Style Technician 5-Tab Fixed Bottom Dock --}}
                        <a href="{{ route('technician.dashboard') }}" 
                           class="flex flex-col items-center justify-center flex-1 py-2 transition {{ request()->routeIs('technician.dashboard') ? 'text-[#2563eb] font-bold' : 'text-[#64748b] hover:text-[#0f172a]' }}">
                            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                            <span class="text-[12px] leading-tight mt-1">{{ __('Home') }}</span>
                        </a>

                        <a href="{{ route('calendar.index') }}" 
                           class="flex flex-col items-center justify-center flex-1 py-2 transition {{ request()->routeIs('calendar.*') ? 'text-[#2563eb] font-bold' : 'text-[#64748b] hover:text-[#0f172a]' }}">
                            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5"/></svg>
                            <span class="text-[12px] leading-tight mt-1">{{ __('Calendar') }}</span>
                        </a>

                        <a href="{{ route('sites.index') }}" 
                           class="flex flex-col items-center justify-center flex-1 py-2 transition {{ request()->routeIs('sites.*') ? 'text-[#2563eb] font-bold' : 'text-[#64748b] hover:text-[#0f172a]' }}">
                            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            <span class="text-[12px] leading-tight mt-1">{{ __('Sites') }}</span>
                        </a>

                        <a href="{{ route('workers.index') }}" 
                           class="flex flex-col items-center justify-center flex-1 py-2 transition {{ (request()->routeIs('workers.*') && !request()->routeIs('workers.attendance.*')) ? 'text-[#2563eb] font-bold' : 'text-[#64748b] hover:text-[#0f172a]' }}">
                            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.999-3.199a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                            <span class="text-[12px] leading-tight mt-1">{{ __('Workers') }}</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex flex-col items-center justify-center flex-1 py-2 transition {{ request()->routeIs('profile.*') ? 'text-[#2563eb] font-bold' : 'text-[#64748b] hover:text-[#0f172a]' }}">
                            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            <span class="text-[12px] leading-tight mt-1">{{ __('Profile') }}</span>
                        </a>

                    @elseif(auth()->user()->isOffice())
                        {{-- Admin & Staff Mobile Dock --}}
                        <a href="{{ route('dashboard') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span class="text-[11px] tracking-tight">Home</span>
                        </a>

                        <button @click="sidebarOpen = true" 
                                type="button"
                                class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="text-[11px] tracking-tight">Modules</span>
                        </button>

                        {{-- Elevated Center Action Button (+) with Mobile Quick Action Sheet --}}
                        <div x-data="{ mobileActionOpen: false }" class="relative -mt-5"
                             x-init="$watch('mobileActionOpen', open => open && $nextTick(() => crmFocusFirst($refs.actionPanel)))"
                             @keydown.escape="if (mobileActionOpen) { mobileActionOpen = false; $refs.actionBtn.focus(); }">
                            <button @click="mobileActionOpen = !mobileActionOpen"
                                    type="button"
                                    x-ref="actionBtn"
                                    aria-haspopup="true"
                                    aria-controls="crm-quick-actions"
                                    :aria-expanded="mobileActionOpen.toString()"
                                    aria-label="Quick actions"
                                    class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/35 border-2 border-white dark:border-[#0f172a] active:scale-95 transition-transform cursor-pointer"
                                    title="Quick Action">
                                <svg class="w-6 h-6 transition-transform duration-200" :class="mobileActionOpen ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                            <span class="block text-center text-[11px] font-bold text-blue-600 dark:text-blue-400 mt-0.5">Create</span>

                            {{-- Mobile Quick Action Bottom Sheet --}}
                            <div x-show="mobileActionOpen"
                                 x-ref="actionPanel"
                                 id="crm-quick-actions"
                                 role="region"
                                 aria-label="Quick actions"
                                 @keydown.tab="crmTrapTab($event, $refs.actionPanel)"
                                 @click.away="mobileActionOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                                 class="absolute bottom-16 left-1/2 -translate-x-1/2 w-64 bg-[var(--card-bg)] border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl p-2 z-50 text-xs font-semibold divide-y divide-slate-100 dark:divide-slate-800"
                                 style="display: none;">
                                <div class="px-3 py-1.5 font-bold text-slate-400 uppercase text-[11px] tracking-wider">Quick Actions</div>
                                <div class="py-1">
                                    @if(auth()->user()->isOffice())
                                    <a href="{{ route('leads.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-blue-600">
                                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 flex items-center justify-center font-bold text-xs" aria-hidden="true">+</span>
                                        New Customer Lead
                                    </a>
                                    @endif
                                    <a href="{{ route('quotations.create-general') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-emerald-600">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center font-bold text-xs" aria-hidden="true">+</span>
                                        Generate Quotation
                                    </a>
                                    @if(auth()->user()->can('create', \App\Models\Project::class))
                                    <a href="{{ route('projects.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-indigo-600">
                                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center font-bold text-xs" aria-hidden="true">+</span>
                                        New Installation Project
                                    </a>
                                    @endif
                                    <a href="{{ route('attendance.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-purple-600">
                                        <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-600 flex items-center justify-center font-bold text-xs" aria-hidden="true"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                                        Attendance & Shift
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('attendance.index') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('attendance.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-[11px] tracking-tight">Roster</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[11px] tracking-tight">Profile</span>
                        </a>
                    @else
                        {{-- Other accounts: profile --}}
                        <a href="{{ route('profile.edit') }}" 
                           class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[11px] tracking-tight">Profile</span>
                        </a>
                    @endif
                </div>
            </nav>
            @endunless

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- KEYBOARD SHORTCUTS HELP (opened with ? or Ctrl+/, closed with Esc) --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="shortcutsModalOpen"
                 x-cloak
                 x-init="$watch('shortcutsModalOpen', open => {
                     if (open) {
                         $el._returnFocus = document.activeElement;
                         $nextTick(() => $refs.shortcutsClose?.focus());
                     } else {
                         $el._returnFocus?.focus?.();
                     }
                 })"
                 x-transition.opacity.duration.150ms
                 @click.self="shortcutsModalOpen = false"
                 @keydown.tab="crmTrapTab($event, $el.querySelector('[role=dialog]'))"
                 class="crm-shortcuts-overlay fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                <div role="dialog" aria-modal="true" aria-labelledby="crm-shortcuts-title" class="crm-shortcuts-panel w-full max-w-md rounded-xl bg-[var(--card-bg)] text-[var(--text-main)]">
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-[var(--card-border)]">
                        <h2 id="crm-shortcuts-title" class="text-sm font-bold">Keyboard Shortcuts</h2>
                        <x-close-button label="Close keyboard shortcuts" x-ref="shortcutsClose" @click="shortcutsModalOpen = false" />
                    </div>
                    @php
                        $crmShortcuts = [
                            [['Ctrl', 'K'], 'Search leads, jobs, quotes, equipment'],
                            [['Ctrl', 'B'], 'Collapse or expand the sidebar'],
                            [['Ctrl', 'Z'], 'Go back to previous page / module'],
                            [['Ctrl', 'X'], 'Go forward to next page / module'],
                            [['Alt', 'T'], 'Switch between day and night mode'],
                            [['Alt', 'D'], 'Go to dashboard'],
                            [['Alt', 'A'], 'Go to attendance'],
                        ];
                        if (auth()->user()->isAdmin() || auth()->user()->isStaff()) {
                            $crmShortcuts[] = [['Alt', 'L'], 'Go to leads'];
                        }
                        $crmShortcuts[] = [['?'], 'Show this help'];
                        $crmShortcuts[] = [['Esc'], 'Close popups and dialogs'];
                    @endphp
                    <ul class="px-5 py-2 text-xs">
                        @foreach($crmShortcuts as [$keys, $description])
                            <li class="flex items-center justify-between gap-4 py-2 border-b border-[var(--card-border)] last:border-b-0">
                                <span class="text-[var(--text-secondary)]">{{ $description }}</span>
                                <span class="flex items-center gap-1 shrink-0">
                                    @foreach($keys as $key)
                                        <kbd class="crm-kbd">{{ $key }}</kbd>
                                    @endforeach
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="px-5 pb-4 text-[11px] text-[var(--text-muted)]">On Mac, use ⌘ instead of Ctrl. Alt shortcuts work when you are not typing in a field.</p>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- GLOBAL FLASH TOASTS: shown only when the page itself did not     --}}
            {{-- already print the message, so every redirect gives feedback      --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @php
                $crmRenderedPage = (string) $slot . (isset($header) ? (string) $header : '');
                // Breeze status codes that the profile/verification views translate themselves
                $crmHandledStatusCodes = ['profile-updated', 'password-updated', 'verification-link-sent'];
                $crmFlashes = collect(['success' => 'success', 'status' => 'success', 'warning' => 'warning', 'error' => 'error'])
                    ->map(fn ($tone, $key) => ['tone' => $tone, 'text' => session($key)])
                    ->filter(fn ($flash) => is_string($flash['text'])
                        && trim($flash['text']) !== ''
                        && ! in_array($flash['text'], $crmHandledStatusCodes, true)
                        && ! str_contains($crmRenderedPage, e($flash['text'])))
                    ->values();
            @endphp
            @if($crmFlashes->isNotEmpty())
                <div class="crm-toast-stack" aria-live="polite">
                    @foreach($crmFlashes as $flash)
                        <div x-data="{ show: true }"
                             @if($flash['tone'] !== 'error') x-init="setTimeout(() => show = false, 6000)" @endif
                             x-show="show"
                             x-transition.opacity.duration.200ms
                             role="{{ $flash['tone'] === 'error' ? 'alert' : 'status' }}"
                             class="crm-toast crm-toast--{{ $flash['tone'] }}">
                            <p class="crm-toast__text">{{ $flash['text'] }}</p>
                            <button type="button" @click="show = false" class="crm-toast__close btn-icon" aria-label="Dismiss message">
                                <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- Progressive Web App (PWA) Offline & Install Handler --}}
        <x-pwa-installer />

        {{-- Quick On-the-Fly Product Catalog Creator (Available across all CRM pages) --}}
        @if(auth()->check() && auth()->user()->isOffice())
            <x-quick-add-product-modal />
        @endif

        {{-- Global Hardware & Product Barcode Scanner Modal with Mobile Cam Pairing (Admin, Staff, Technician only) --}}
        @if(auth()->check() && auth()->user()->isInternal())
            <x-hardware-scanner-modal modalId="hardwareScannerModal" />
        @endif

        {{-- Universal Searchable Select Combobox Engine for All CRM Modules --}}
        <x-crm-searchable-script />

        {{-- Universal Contact Saver Engine (Mobile Android Intent / iOS / Desktop Google Contacts & .vcf) --}}
        <x-contact-saver />

        {{-- Universal Voice Dictation & Speech Recognition Engine (Available for Technicians & Field App) --}}
        @if(auth()->check() && (auth()->user()->isTechnician() || auth()->user()->isWorker()))
            <x-tech-voice-engine />
        @endif

    </body>
</html>
