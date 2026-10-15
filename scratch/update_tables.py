path = 'resources/views/layouts/app.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

target = '''            table th,
            table td {
                border-bottom: 1.5px solid #1e293b !important;
            }
            html.dark table th,
            html.dark table td {
                border-bottom: 1.5px solid #334155 !important;
            }

            table th:not(:last-child),
            table td:not(:last-child) {
                border-right: 1px solid #1e293b !important;
            }
            html.dark table th:not(:last-child),
            html.dark table td:not(:last-child) {
                border-right: 1px solid #334155 !important;
            }'''

replacement = '''            table tbody tr,
            table tr,
            table th,
            table td {
                border-bottom: 1.5px solid #1e293b !important;
            }
            html.dark table tbody tr,
            html.dark table tr,
            html.dark table th,
            html.dark table td {
                border-bottom: 1.5px solid #334155 !important;
            }

            table th:not(:last-child),
            table td:not(:last-child) {
                border-right: 1.5px solid #1e293b !important;
            }
            html.dark table th:not(:last-child),
            html.dark table td:not(:last-child) {
                border-right: 1.5px solid #334155 !important;
            }
            table th:last-child,
            table td:last-child {
                border-right: none !important;
            }

            /* Omnisearch & Search Bar Border System */
            .crm-search-input,
            header input[type="text"],
            input[placeholder*="Search"] {
                border: 1.5px solid #1e293b !important;
                border-color: #1e293b !important;
            }
            html.dark .crm-search-input,
            html.dark header input[type="text"],
            html.dark input[placeholder*="Search"] {
                border: 1.5px solid #475569 !important;
                border-color: #475569 !important;
            }'''

if target in content:
    content = content.replace(target, replacement)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated table and search bar rules successfully")
else:
    print("Target not found")
