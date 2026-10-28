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
        'calendar.*' => 'Calendar',
        'leads.*' => 'Leads & Inquiries',
        'site-surveys.*' => 'Site Surveys',
        'quotations.*' => 'Quotations',
        'estimator.*' => 'CCTV Storage Estimator',
        'projects.*' => 'Projects',
        'sites.*' => 'Installation Sites',
        'workers.attendance.*' => 'Worker Attendance',
        'workers.*' => 'Daily Wage Workers',
        'wages.*' => 'Sunday Payday Hub',
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
        'employee.hub' => 'Employee & Workspace',
        'attendance.*' => 'Attendance',
        'leaves.*' => 'Leaves & Approvals',
        'finance.hub' => 'Finance & Accounting',
        'finance.analytics' => 'Finance Analytics',
        'finance.expenses.*' => 'Expenses',
        'finance.gst.*' => 'GST',
        'finance.job-costing.*' => 'Job Costing',
        'finance.payables.*' => 'Payables',
        'finance.receivables.*' => 'Receivables',
        'finance.payroll.*' => 'Payroll',
        'finance.petty_cash.*' => 'Petty Cash',
        'finance.salaries.*' => 'Salaries',
        'finance.*' => 'Finance & Accounting',
        'invoices.*' => 'Invoices',
        'payments.*' => 'Payments',
        'payment.*' => 'Payments',
        'alerts.*' => 'Automated Alerts & SMS',
        'analytics.*' => 'Executive Analytics',
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
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-x-hidden max-w-full {{ $isDarkServer ? 'dark' : '' }}" data-theme="{{ $isDarkServer ? 'dark' : 'light' }}" style="background-color: var(--page-bg); color: var(--text-main);">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $crmPageTitle }} | {{ config('app.name', 'Precision IT Systems CRM') }}</title>

        <!-- Favicon / App Icon -->
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}?v={{ filemtime(public_path('logo.png')) }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
        <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

        <!-- Dynamic Logo Engine -->
        <x-crm-theme-init :userId="auth()->id()" :userAccent="auth()->user()->theme_accent" :userMode="$activeMode" :userStyle="auth()->user()->theme_style" />

        <!-- Scripts & Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* ═══════════════════════════════════════════════════════════════════════
               SECUREVISION AI CRM — THEME VARIABLES SYSTEM
               ═══════════════════════════════════════════════════════════════════════ */
            :root,
            [data-theme='light'],
            html:not(.dark) {
                --page-bg: #f1f5f9;
                --bg-canvas: #f1f5f9;

                --card-bg: #ffffff;
                --bg-card: #ffffff;

                --card-border: #cbd5e1;
                --border-card: #cbd5e1;

                --input-border: #94a3b8;
                --border-input: #94a3b8;
                --button-border: #94a3b8;

                --table-header-bg: #e2e8f0;
                --bg-table-header: #e2e8f0;

                --table-border: #cbd5e1;
                --border-table: #cbd5e1;
                --border-dark-line: #cbd5e1;

                --text-main: #0f172a;
                --text-muted: #64748b;
                --text-secondary: #475569;
                --text-hint: #64748b;

                --status-green: #16a34a;
                --status-orange: #d97706;
                --status-red: #dc2626;
                --status-success: #16a34a;
                --status-warning: #d97706;
                --status-danger: #dc2626;

                --primary-btn-bg: #2563eb;
                --primary-btn-hover: #1d4ed8;
                --primary-btn-text: #ffffff;
                --crm-accent: #2563eb;
                --crm-accent-hover: #1d4ed8;
                --crm-accent-light: #eff6ff;
                --brand-blue: #2563eb;
                --brand-blue-hover: #1d4ed8;
            }

            [data-theme='dark'],
            html.dark,
            html[data-theme='dark'] {
                --page-bg: #0f172a;
                --bg-canvas: #0f172a;

                --card-bg: #1e293b;
                --bg-card: #1e293b;

                --card-border: #334155;
                --border-card: #334155;

                --input-border: #334155;
                --border-input: #334155;
                --button-border: #334155;

                --table-header-bg: #1e293b;
                --bg-table-header: #1e293b;

                --table-border: #334155;
                --border-table: #334155;
                --border-dark-line: #334155;

                --text-main: #e2e8f0; /* strictly no pure white */
                --text-muted: #94a3b8;
                --text-secondary: #94a3b8;
                --text-hint: #94a3b8;

                --status-green: #4ade80;
                --status-orange: #fbbf24;
                --status-red: #f87171;
                --status-success: #4ade80;
                --status-warning: #fbbf24;
                --status-danger: #f87171;

                --primary-btn-bg: #2563eb; /* primary button stays #2563eb */
                --primary-btn-hover: #1d4ed8;
                --primary-btn-text: #ffffff;
                --crm-accent: #2563eb;
                --crm-accent-hover: #1d4ed8;
                --crm-accent-light: rgba(37, 99, 235, 0.15);
                --brand-blue: #2563eb;
                --brand-blue-hover: #1d4ed8;
            }


            html {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
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

            /* Distinct Focus Ring */
            input:focus:not([class*="border-0"]),
            select:focus:not([class*="border-0"]),
            textarea:focus:not([class*="border-0"]),
            .crm-input:focus,
            .crm-select:focus,
            .crm-search-input:focus,
            .crm-cal-input:focus,
            .crm-cal-select:focus,
            .ts-control.focus,
            .ts-control:focus-within {
                border-color: #2563eb !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.28) !important;
                outline: none !important;
            }
            html.dark input:focus:not([class*="border-0"]),
            html.dark select:focus:not([class*="border-0"]),
            html.dark textarea:focus:not([class*="border-0"]),
            html.dark .crm-input:focus,
            html.dark .crm-select:focus,
            html.dark .crm-search-input:focus,
            html.dark .crm-cal-input:focus,
            html.dark .crm-cal-select:focus,
            html.dark .ts-control.focus,
            html.dark .ts-control:focus-within {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.35) !important;
                outline: none !important;
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

            /* Header Bar: Defined Dark Border Bottom */
            header.crm-topbar,
            .crm-topbar {
                background-color: var(--card-bg) !important;
                border-bottom: 1px solid var(--table-border) !important;
            }
            html.dark header.crm-topbar,
            html.dark .crm-topbar {
                background-color: var(--card-bg) !important;
                border-bottom: 1px solid var(--table-border) !important;
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
                border-color: #2563eb !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.28) !important;
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
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.35) !important;
                outline: none !important;
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

            /* Sidebar: Defined Dark Border Right and Dividers (Light Mode) */
            html:not(.dark) .crm-sidebar-container,
            html:not(.dark) aside {
                background-color: var(--card-bg) !important;
                border-right: 1px solid var(--table-border) !important;
            }
            html:not(.dark) .crm-sidebar-header {
                background-color: var(--card-bg) !important;
                border-bottom: 1px solid var(--table-border) !important;
            }
            html:not(.dark) .crm-sidebar-footer {
                background-color: var(--card-bg) !important;
                border-top: 1px solid var(--card-border) !important;
            }
            html:not(.dark) aside nav a.crm-active-link,
            html:not(.dark) .crm-active-link,
            html:not(.dark) aside nav a:active {
                background: var(--primary-btn-bg) !important;
                background-color: var(--primary-btn-bg) !important;
                border-radius: 8px !important;
                color: var(--text-main) !important;
                font-weight: 700 !important;
                box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3) !important;
            }
            html:not(.dark) aside nav a.crm-active-link svg,
            html:not(.dark) .crm-active-link svg,
            html:not(.dark) aside nav a.crm-active-link svg *,
            html:not(.dark) .crm-active-link svg * {
                color: var(--text-main) !important;
                stroke: #ffffff !important;
            }
            html:not(.dark) aside nav a:hover:not(.crm-active-link) {
                background-color: #eff6ff !important;
                color: #2563eb !important;
            }
            html:not(.dark) aside nav a:hover:not(.crm-active-link) svg,
            html:not(.dark) aside nav a:hover:not(.crm-active-link) svg * {
                color: #2563eb !important;
                stroke: #2563eb !important;
            }
            html:not(.dark) aside nav a:not(.crm-active-link):not(:hover) {
                color: var(--text-main) !important;
                font-weight: 600 !important;
            }
            html:not(.dark) aside nav a:not(.crm-active-link):not(:hover) svg,
            html:not(.dark) aside nav a:not(.crm-active-link):not(:hover) svg * {
                color: #475569 !important;
                stroke: #475569 !important;
            }
            html:not(.dark) aside div.uppercase {
                color: var(--text-muted) !important;
                font-weight: 700 !important;
            }

            /* Sidebar: Defined High-Contrast Proper Dark Borders & Clean Readable Typography (Dark Mode) */
            html.dark .crm-sidebar-container,
            html.dark aside {
                background-color: var(--table-header-bg) !important;
                border-right: 1px solid var(--table-border) !important;
            }
            html.dark .crm-sidebar-header {
                background-color: var(--card-bg) !important;
                border-bottom: 1px solid var(--table-border) !important;
            }
            html.dark .crm-sidebar-footer {
                background-color: var(--card-bg) !important;
                border-top: 1px solid var(--card-border) !important;
            }
            html.dark aside nav a.crm-active-link,
            html.dark .crm-active-link {
                background-color: var(--primary-btn-bg) !important;
                color: var(--text-main) !important;
                font-weight: 700 !important;
                box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3) !important;
            }
            html.dark aside nav a.crm-active-link svg,
            html.dark .crm-active-link svg,
            html.dark aside nav a.crm-active-link svg *,
            html.dark .crm-active-link svg * {
                color: var(--text-main) !important;
                stroke: #ffffff !important;
            }
            html.dark aside nav a:hover:not(.crm-active-link) {
                background-color: var(--table-header-bg) !important;
                color: var(--text-main) !important;
            }
            html.dark aside nav a:hover:not(.crm-active-link) svg,
            html.dark aside nav a:hover:not(.crm-active-link) svg * {
                color: #60a5fa !important;
                stroke: #60a5fa !important;
            }
            html.dark aside nav a:not(.crm-active-link):not(:hover) {
                color: #e2e8f0 !important; /* Crisp, clearly readable text */
                font-weight: 500 !important;
            }
            html.dark aside nav a:not(.crm-active-link):not(:hover) svg,
            html.dark aside nav a:not(.crm-active-link):not(:hover) svg * {
                color: var(--text-muted) !important; /* Visible clean icons */
                stroke: #94a3b8 !important;
            }
            html.dark aside div.uppercase {
                color: var(--text-muted) !important; /* Distinct readable category titles */
                font-weight: 700 !important;
                letter-spacing: 0.05em !important;
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
                background-color: #2563eb !important;
                border: 1px solid #2563eb !important;
                color: #ffffff !important;
            }
            .btn-primary:hover,
            .crm-btn-primary:hover,
            .crm-filter-btn:hover {
                background-color: #1d4ed8 !important;
                border-color: #1d4ed8 !important;
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
                background-color: #2563eb !important;
                border: 1px solid #2563eb !important;
                color: #ffffff !important;
                cursor: pointer !important;
                text-decoration: none !important;
                white-space: nowrap !important;
                transition: all 0.15s ease !important;
                box-shadow: 0 1px 2px rgba(37, 99, 235, 0.25) !important;
            }
            .btn-subtle:hover,
            .btn-inline-add:hover,
            a.btn-subtle:hover,
            button.btn-subtle:hover,
            a[class*="btn-subtle"]:hover,
            button[class*="btn-subtle"]:hover {
                background-color: #1d4ed8 !important;
                border-color: #1d4ed8 !important;
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
                background-color: #2563eb !important;
                border: 1px solid #3b82f6 !important;
                color: #ffffff !important;
            }
            html.dark .btn-subtle:hover,
            html.dark .btn-inline-add:hover,
            html.dark a.btn-subtle:hover,
            html.dark button.btn-subtle:hover,
            html.dark a[class*="btn-subtle"]:hover,
            html.dark button[class*="btn-subtle"]:hover {
                background-color: #1d4ed8 !important;
                border-color: #2563eb !important;
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
                color: #60a5fa !important;
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
            .crm-select:focus {
                border-color: #2563eb !important;
                outline: none !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.28) !important;
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
            input[type="search"],
            input[name="search"]:not([type="hidden"]) {
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
            html.dark input[type="search"],
            html.dark input[name="search"]:not([type="hidden"]) {
                background-color: var(--card-bg) !important;
                border: 1px solid var(--input-border) !important;
                color: var(--text-main) !important;
            }

            .crm-search-bar:focus,
            .search-input-box:focus,
            input[type="search"]:focus,
            input[name="search"]:not([type="hidden"]):focus {
                border-color: #2563eb !important;
                outline: none !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.28) !important;
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
            <div class="crm-app-container min-h-screen flex bg-[var(--page-bg)] text-[var(--text-main)] transition-colors duration-200">
                
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
                <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[var(--page-bg)] text-[var(--text-main)] transition-colors duration-200">
                    
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

                    {{-- Fixed Top Bar: 56px high --}}
                    <header class="crm-topbar h-[56px] min-h-[56px] max-h-[56px] bg-[var(--card-bg)] text-[var(--text-main)] border-b border-[var(--card-border)] px-5 flex items-center justify-between sticky top-0 z-10"
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

                            {{-- Desktop Sidebar Collapse Toggle (same action as Ctrl+B) --}}
                            <button @click="window.crmSidebar.toggle()"
                                    type="button"
                                    id="header-sidebar-toggle-btn"
                                    class="hidden lg:inline-flex items-center justify-center w-8 h-8 rounded-[6px] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer shrink-0"
                                    :title="sidebarCollapsed ? 'Expand sidebar (Ctrl+B)' : 'Collapse sidebar (Ctrl+B)'"
                                    :aria-expanded="(!sidebarCollapsed).toString()"
                                    aria-label="Toggle sidebar">
                                <svg class="w-[18px] h-[18px] pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                                    <line stroke-linecap="round" x1="9" x2="9" y1="3" y2="21"/>
                                </svg>
                            </button>

                            <span class="crm-page-title text-sm font-bold text-[var(--text-main)] truncate">{{ $crmPageTitle }}</span>
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
                                            <button @click="clear()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold leading-none p-0.5 cursor-pointer">
                                                &times;
                                            </button>
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
                                                        <div class="flex items-center justify-between gap-1">
                                                            <p class="text-xs font-bold text-[var(--text-main)] truncate" x-text="item.title"></p>
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
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-[6px] border border-[var(--button-border)] bg-[var(--card-bg)] text-[var(--text-main)] hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer shrink-0 shadow-2xs" 
                                    :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'" 
                                    aria-label="Toggle dark/light mode">
                                {{-- Sun Icon (shown when dark mode is active) --}}
                                <svg x-show="isDarkMode" x-cloak class="w-4 h-4 text-amber-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                <svg x-show="!isDarkMode" class="w-4 h-4 text-slate-700 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                        class="relative inline-flex items-center justify-center w-8 h-8 rounded-[6px] border border-[var(--button-border)] bg-[var(--card-bg)] text-[var(--text-main)] hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer shrink-0 shadow-2xs" 
                                        title="Notifications & System Alerts" 
                                        aria-label="View notifications">
                                    <svg class="w-4 h-4 text-[var(--text-main)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount" 
                                          class="absolute -top-1.5 -right-1.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-black text-white bg-rose-500 rounded-[4px] leading-none shadow-xs">
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
                                     class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-[#0f172a] rounded-xl border border-[#1e293b] dark:border-slate-600 shadow-xl py-0 z-50 overflow-hidden text-xs text-slate-700 dark:text-slate-200"
                                     style="display: none;">
                                    
                                    {{-- Popover Header --}}
                                    <div class="px-4 py-3 border-b border-[#1e293b] dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex items-center justify-between">
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
                                        class="flex items-center gap-2 pl-1.5 pr-2.5 py-1 rounded-[6px] border border-[#1e293b] dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition cursor-pointer select-none shadow-2xs">
                                    @if(Auth::user()->avatar_url)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700">
                                    @else
                                        <div class="w-7 h-7 rounded-full text-white flex items-center justify-center font-bold text-xs shrink-0" style="background-color: var(--crm-accent, #2563eb);">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="text-left hidden sm:block min-w-0 pr-0.5">
                                        <span class="block text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                        <span class="block text-[10px] text-blue-600 dark:text-blue-400 font-semibold leading-tight truncate">{{ Auth::user()->role_title }}</span>
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
                                     class="absolute right-0 mt-2 w-60 bg-white dark:bg-[#0f172a] rounded-xl border border-[#1e293b] dark:border-slate-600 shadow-xl py-1 z-50 text-xs font-medium text-slate-700 dark:text-slate-200 overflow-hidden"
                                     style="display: none;">
                                    
                                    {{-- User identity card in menu --}}
                                    <div class="px-3.5 py-2.5 border-b border-[#1e293b] dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex items-center gap-3">
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

                                        <button type="button" @click="userMenuOpen = false; window.crmOpenShortcuts()" class="hidden lg:flex w-full text-left items-center justify-between px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">
                                            <span class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 9h.01M10 9h.01M14 9h.01M18 9h.01M6 13h.01M18 13h.01M9 15h6"/></svg>
                                                <span>Keyboard Shortcuts</span>
                                            </span>
                                            <kbd class="crm-kbd">?</kbd>
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
                                       style="height: 38px !important; border-radius: 6px !important;"
                                       class="w-full px-3 text-xs bg-white dark:bg-slate-900 border border-[#1e293b] dark:border-slate-600 rounded text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none">
                            </div>

                            <button @click="mobileSearchOpen = false; open = false;" 
                                    type="button" 
                                    class="px-2 py-1 rounded text-xs font-semibold text-slate-600 dark:text-slate-300 shrink-0">
                                Cancel
                            </button>
                        </div>
                    </header>

                    {{-- Main Content Area: Responsive padding p-3 on mobile, p-5 on desktop --}}
                    <main class="flex-1 overflow-y-auto overflow-x-hidden bg-[#f8fafc] dark:bg-[#060913] p-3 sm:p-5 transition-colors duration-200 pb-28 lg:pb-8">
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
                 class="crm-shortcuts-overlay fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                <div role="dialog" aria-modal="true" aria-labelledby="crm-shortcuts-title" class="crm-shortcuts-panel w-full max-w-md rounded-xl bg-[var(--card-bg)] text-[var(--text-main)]">
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-[var(--card-border)]">
                        <h2 id="crm-shortcuts-title" class="text-sm font-bold">Keyboard Shortcuts</h2>
                        <button type="button" x-ref="shortcutsClose" @click="shortcutsModalOpen = false" class="crm-shortcuts-close btn-icon" aria-label="Close keyboard shortcuts">
                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @php
                        $crmShortcuts = [
                            [['Ctrl', 'K'], 'Search leads, jobs, quotes, equipment'],
                            [['Ctrl', 'B'], 'Collapse or expand the sidebar'],
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

    </body>
</html>
