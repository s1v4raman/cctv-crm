import os
import re

print("Starting theme variables migration...")

# =========================================================================
# STEP 1: Update resources/views/components/crm-theme-init.blade.php
# =========================================================================
init_path = 'resources/views/components/crm-theme-init.blade.php'
with open(init_path, 'r', encoding='utf-8') as f:
    init_content = f.read()

# Ensure data-theme is synchronized on document.documentElement
init_content = init_content.replace(
    "document.documentElement.classList.add('dark');",
    "document.documentElement.classList.add('dark'); document.documentElement.setAttribute('data-theme', 'dark');"
)
init_content = init_content.replace(
    "document.documentElement.classList.remove('dark');",
    "document.documentElement.classList.remove('dark'); document.documentElement.setAttribute('data-theme', 'light');"
)

with open(init_path, 'w', encoding='utf-8') as f:
    f.write(init_content)
print("[OK] Synchronized data-theme attribute in crm-theme-init.blade.php")


# =========================================================================
# STEP 2: Update resources/views/layouts/app.blade.php
# =========================================================================
layout_path = 'resources/views/layouts/app.blade.php'
with open(layout_path, 'r', encoding='utf-8') as f:
    layout_content = f.read()

# Update <html ...> tag
layout_content = re.sub(
    r'<html\s+lang="[^"]*"\s+class="[^"]*">',
    r'<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}" class="h-full {{ $isDarkServer ? \'dark\' : \'\' }}" data-theme="{{ $isDarkServer ? \'dark\' : \'light\' }}" style="background-color: var(--page-bg); color: var(--text-main);">',
    layout_content
)

# Theme CSS Variables definition block
theme_variables_css = """
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
"""

def replace_hardcoded_colors_in_css(css):
    # If variables not present, inject them
    if "--page-bg:" not in css:
        css = theme_variables_css + "\n" + css

    # Replace hardcoded card backgrounds and borders
    css = re.sub(r'background:\s*#ffffff\s*!important;', 'background: var(--card-bg) !important;', css)
    css = re.sub(r'background-color:\s*#ffffff\s*!important;', 'background-color: var(--card-bg) !important;', css)
    css = re.sub(r'background:\s*#0f172a\s*!important;', 'background: var(--card-bg) !important;', css)
    css = re.sub(r'background-color:\s*#0f172a\s*!important;', 'background-color: var(--card-bg) !important;', css)
    css = re.sub(r'border:\s*1px\s+solid\s+#1e293b\s*!important;', 'border: 1px solid var(--card-border) !important;', css)
    css = re.sub(r'border-color:\s*#1e293b\s*!important;', 'border-color: var(--card-border) !important;', css)
    css = re.sub(r'border:\s*1px\s+solid\s+#334155\s*!important;', 'border: 1px solid var(--card-border) !important;', css)
    css = re.sub(r'border-color:\s*#334155\s*!important;', 'border-color: var(--card-border) !important;', css)

    # Replace hardcoded table headers and cells
    css = re.sub(r'background-color:\s*#f1f5f9\s*!important;', 'background-color: var(--table-header-bg) !important;', css)
    css = re.sub(r'background-color:\s*#0b1120\s*!important;', 'background-color: var(--table-header-bg) !important;', css)
    css = re.sub(r'background-color:\s*#1e293b\s*!important;', 'background-color: var(--table-header-bg) !important;', css)
    css = re.sub(r'border-bottom:\s*1px\s+solid\s+#1e293b\s*!important;', 'border-bottom: 1px solid var(--table-border) !important;', css)
    css = re.sub(r'border-bottom:\s*1px\s+solid\s+#334155\s*!important;', 'border-bottom: 1px solid var(--table-border) !important;', css)
    css = re.sub(r'border-right:\s*1px\s+solid\s+#1e293b\s*!important;', 'border-right: 1px solid var(--table-border) !important;', css)
    css = re.sub(r'border-right:\s*1px\s+solid\s+#334155\s*!important;', 'border-right: 1px solid var(--table-border) !important;', css)
    css = re.sub(r'border-top:\s*1px\s+solid\s+#1e293b\s*!important;', 'border-top: 1px solid var(--card-border) !important;', css)
    css = re.sub(r'border-top:\s*1px\s+solid\s+#334155\s*!important;', 'border-top: 1px solid var(--card-border) !important;', css)

    # Replace hardcoded input borders
    css = re.sub(r'border:\s*1px\s+solid\s+#475569\s*!important;', 'border: 1px solid var(--input-border) !important;', css)
    css = re.sub(r'border-color:\s*#475569\s*!important;', 'border-color: var(--input-border) !important;', css)
    css = re.sub(r'scrollbar-color:\s*#94a3b8\s+transparent;', 'scrollbar-color: var(--input-border) transparent;', css)
    css = re.sub(r'scrollbar-color:\s*#334155\s+transparent;', 'scrollbar-color: var(--input-border) transparent;', css)

    # Replace hardcoded text colors
    css = re.sub(r'color:\s*#0f172a\s*!important;', 'color: var(--text-main) !important;', css)
    css = re.sub(r'color:\s*#f8fafc\s*!important;', 'color: var(--text-main) !important;', css)
    css = re.sub(r'color:\s*#ffffff\s*!important;', 'color: var(--text-main) !important;', css)
    css = re.sub(r'color:\s*#1e293b\s*!important;', 'color: var(--text-main) !important;', css)
    css = re.sub(r'color:\s*#cbd5e1\s*!important;', 'color: var(--text-muted) !important;', css)
    css = re.sub(r'color:\s*#94a3b8\s*!important;', 'color: var(--text-muted) !important;', css)
    css = re.sub(r'color:\s*#64748b\s*!important;', 'color: var(--text-muted) !important;', css)

    # Replace hardcoded status colors
    css = re.sub(r'color:\s*#16a34a\s*!important;', 'color: var(--status-green) !important;', css)
    css = re.sub(r'color:\s*#4ade80\s*!important;', 'color: var(--status-green) !important;', css)
    css = re.sub(r'color:\s*#d97706\s*!important;', 'color: var(--status-orange) !important;', css)
    css = re.sub(r'color:\s*#fbbf24\s*!important;', 'color: var(--status-orange) !important;', css)
    css = re.sub(r'color:\s*#dc2626\s*!important;', 'color: var(--status-red) !important;', css)
    css = re.sub(r'color:\s*#f87171\s*!important;', 'color: var(--status-red) !important;', css)

    # Primary buttons
    css = re.sub(r'background-color:\s*#2563eb\s*!important;', 'background-color: var(--primary-btn-bg) !important;', css)
    css = re.sub(r'background:\s*#2563eb\s*!important;', 'background: var(--primary-btn-bg) !important;', css)

    return css

# Replace inside <style> of app.blade.php
style_match = re.search(r'<style>(.*?)</style>', layout_content, re.DOTALL)
if style_match:
    updated_style = replace_hardcoded_colors_in_css(style_match.group(1))
    layout_content = layout_content[:style_match.start(1)] + updated_style + layout_content[style_match.end(1):]

with open(layout_path, 'w', encoding='utf-8') as f:
    f.write(layout_content)
print("[OK] Updated layouts/app.blade.php with theme variables in <style>")


# =========================================================================
# STEP 3: Update resources/css/app.css
# =========================================================================
css_path = 'resources/css/app.css'
with open(css_path, 'r', encoding='utf-8') as f:
    css_content = f.read()

# Replace root blocks at top of app.css
root_defs = """:root,
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
}"""

css_content = re.sub(
    r':root\s*\{[^}]*\}\s*html\s*\{[^}]*\}\s*html\.dark\s*\{[^}]*\}',
    root_defs,
    css_content,
    flags=re.DOTALL
)

# Apply color replacements across app.css
css_content = replace_hardcoded_colors_in_css(css_content)

# Append comprehensive theme variable bindings
css_content += """

/* ═══════════════════════════════════════════════════════════════════════
   THEME VARIABLES ENFORCEMENT (Both Light and Dark Theme on All Pages)
   ═══════════════════════════════════════════════════════════════════════ */
body, html, main, .pg-wrap {
    background-color: var(--page-bg) !important;
    color: var(--text-main) !important;
}

.pg-card,
.table-container,
.saas-card,
.crm-section,
.stat-card,
.card,
.section-box {
    background-color: var(--card-bg) !important;
    border: 1px solid var(--card-border) !important;
    color: var(--text-main) !important;
}

.filter-bar,
form.filter-bar,
.crm-filter-bar {
    background-color: var(--card-bg) !important;
    border-bottom: 1px solid var(--card-border) !important;
}

table thead,
table thead tr,
table thead th,
table th,
.crm-table thead th,
.detail-table thead th,
.leads-table thead th,
.jobs-table thead th,
.q-table thead th,
.p-table thead th {
    background-color: var(--table-header-bg) !important;
    border-bottom: 1px solid var(--table-border) !important;
    border-right: 1px solid var(--table-border) !important;
    color: var(--text-muted) !important;
}

table tbody td,
table td,
.crm-table tbody td,
.detail-table tbody td,
.leads-table tbody td,
.jobs-table tbody td,
.q-table tbody td,
.p-table tbody td {
    background-color: var(--card-bg) !important;
    border-bottom: 1px solid var(--table-border) !important;
    border-right: 1px solid var(--table-border) !important;
    color: var(--text-main) !important;
}

input[type="text"]:not([class*="bg-transparent"]):not([class*="border-0"]),
input[type="search"]:not([class*="bg-transparent"]):not([class*="border-0"]),
input[type="email"]:not([class*="bg-transparent"]):not([class*="border-0"]),
input[type="password"]:not([class*="bg-transparent"]):not([class*="border-0"]),
input[type="number"]:not([class*="bg-transparent"]):not([class*="border-0"]),
input[type="date"]:not([class*="bg-transparent"]):not([class*="border-0"]),
input[type="tel"]:not([class*="bg-transparent"]):not([class*="border-0"]),
select:not([class*="border-0"]),
textarea:not([class*="border-0"]),
.crm-input,
.crm-select,
.crm-search-input,
.filter-bar input,
.filter-bar select {
    background-color: var(--card-bg) !important;
    border: 1px solid var(--input-border) !important;
    border-color: var(--input-border) !important;
    color: var(--text-main) !important;
}

.btn-secondary,
.crm-btn-secondary,
.btn-white,
.filter-bar a,
button.btn-secondary,
a.btn-secondary {
    background-color: var(--card-bg) !important;
    border: 1px solid var(--button-border) !important;
    border-color: var(--button-border) !important;
    color: var(--text-main) !important;
}

.btn-primary,
.crm-btn-primary,
.btn-blue,
button.btn-primary,
a.btn-primary {
    background-color: var(--primary-btn-bg) !important;
    border: 1px solid var(--primary-btn-bg) !important;
    color: var(--primary-btn-text) !important;
}

.status-won, .status-accepted, .status-completed, .status-paid, .badge-completed, .badge-won, .badge-paid, [class*="status-active"], .status-green {
    color: var(--status-green) !important;
}
.status-pending, .status-quoted, .status-scheduled, .status-in-progress, .status-in_progress, .badge-pending, .badge-scheduled, .badge-in_progress, .status-contacted {
    color: var(--status-orange) !important;
}
.status-lost, .status-declined, .status-cancelled, .status-canceled, .status-overdue, .badge-cancelled, .badge-lost {
    color: var(--status-red) !important;
}
"""

with open(css_path, 'w', encoding='utf-8') as f:
    f.write(css_content)
print("[OK] Updated resources/css/app.css with theme variables")


# =========================================================================
# STEP 4: Update View Files with hardcoded colors
# =========================================================================
view_files = [
    'resources/views/leads/show.blade.php',
    'resources/views/leads/index.blade.php',
    'resources/views/jobs/index.blade.php',
    'resources/views/jobs/show.blade.php',
    'resources/views/quotations/index.blade.php',
    'resources/views/quotations/show.blade.php',
    'resources/views/equipment/index.blade.php',
    'resources/views/equipment/show.blade.php',
    'resources/views/products/index.blade.php',
    'resources/views/inventory/index.blade.php',
    'resources/views/amcs/index.blade.php',
    'resources/views/amcs/show.blade.php',
    'resources/views/invoices/index.blade.php',
    'resources/views/invoices/show.blade.php',
    'resources/views/service_tickets/index.blade.php',
    'resources/views/attendance/index.blade.php',
    'resources/views/dashboard.blade.php',
    'resources/views/calendar/index.blade.php',
    'resources/views/estimator/index.blade.php',
    'resources/views/admin/users/index.blade.php',
    'resources/views/finance/payables/index.blade.php',
    'resources/views/finance/expenses/index.blade.php',
    'resources/views/finance/gst/index.blade.php',
    'resources/views/finance/receivables/index.blade.php',
    'resources/views/technician/dashboard.blade.php',
    'resources/views/sites/show.blade.php',
]

for vf in view_files:
    if os.path.exists(vf):
        with open(vf, 'r', encoding='utf-8') as f:
            v_code = f.read()

        # Update inline styles like color: #222 / #555
        v_code = re.sub(r'color:\s*#222(?:222)?\s*;', 'color: var(--text-main);', v_code)
        v_code = re.sub(r'color:\s*#555(?:555)?\s*;', 'color: var(--text-muted);', v_code)
        v_code = re.sub(r'color:\s*#1e293b\s*;', 'color: var(--text-main);', v_code)
        v_code = re.sub(r'color:\s*#0f172a\s*;', 'color: var(--text-main);', v_code)
        v_code = re.sub(r'color:\s*#f8fafc\s*;', 'color: var(--text-main);', v_code)
        v_code = re.sub(r'color:\s*#cbd5e1\s*;', 'color: var(--text-muted);', v_code)
        v_code = re.sub(r'color:\s*#94a3b8\s*;', 'color: var(--text-muted);', v_code)

        # Update border colors in view style blocks
        v_code = v_code.replace('#1e293b', 'var(--card-border)')
        v_code = v_code.replace('#334155', 'var(--card-border)')

        with open(vf, 'w', encoding='utf-8') as f:
            f.write(v_code)
        print(f"[OK] Updated {vf}")

print("Theme variable migration completed successfully!")
