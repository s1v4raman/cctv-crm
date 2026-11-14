{{--
    Shared look and behaviour for record list pages (Leads, Quotations): full-width band with
    views and tools, filter panel, list table or board, and the records footer. Classes start
    with ld-; the Alpine helper is listPage(rowIds, storageKey, defaultColumns).
--}}
{{-- This page uses the whole width under the top bar: a white band for views and tools,
     then the filter panel and the list on the grey page. --}}
<style>
    #main-content { padding: 0 0 7rem !important; }
    @media (min-width: 1024px) { #main-content { padding-bottom: 0 !important; } }
    .crm-content { max-width: none !important; }

    .ld-root {
        /* the app-wide tokens in app.css, so these lists match every other page in day and night mode */
        --ld-page: var(--page-bg); --ld-card: var(--card-bg); --ld-border: var(--card-border); --ld-line: var(--table-border);
        --ld-text: var(--text-main); --ld-muted: var(--text-muted); --ld-hover: var(--row-hover); --ld-input: var(--input-border);
        --ld-grey-btn: var(--tool-hover);
        /* buttons and switched-on toggles follow the accent colour picked in Profile → Theme */
        --ld-accent: var(--crm-accent, #2563eb); --ld-accent-hover: var(--crm-accent-hover, #1d4ed8);
        --ld-accent-edge: color-mix(in srgb, var(--ld-accent) 82%, #000);
        --ld-accent-soft: color-mix(in srgb, var(--ld-accent) 12%, #fff);
        --ld-chip: var(--ld-accent-soft);
        background: var(--ld-page); color: var(--ld-text); font-size: 14px; line-height: 1.45;
    }
    html.dark .ld-root {
        --ld-accent-soft: color-mix(in srgb, var(--ld-accent) 22%, var(--card-bg));
    }

    /* app-wide buttons take their height and corners from --control-height and --radius-control */
    .ld-root { --control-height: 30px; --radius-control: 4px; }
    .ld-root .ld-accent, .ld-root .ld-outline { --control-height: 32px; }
    .ld-above { background: var(--ld-card); padding: 2px 16px 0; }
    .ld-above nav { margin-bottom: 0 !important; }
    .ld-band { background: var(--ld-card); border-bottom: 1px solid var(--ld-border); }
    .ld-views { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-bottom: 1px solid var(--ld-border); }
    .ld-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 10px; border-radius: 4px; background: var(--ld-chip); font-size: 13px; font-weight: 600; color: var(--ld-text); }
    .ld-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 16px; min-height: 48px; flex-wrap: wrap; }
    
    /* Native Scrollbar Silencing for Clean App View */
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

    @media (max-width: 768px) {
        /* Mobile App View: Hide legacy desktop view chip & 3-dots to prevent duplicates */
        .ld-views { 
            display: none !important; 
        }
        .ld-views .ld-chip,
        .ld-chip { 
            display: none !important; 
        }
        .ld-views .ld-icon-btn,
        .ld-views .relative:has(> .ld-icon-btn) {
            display: none !important;
        }

        /* Unified Mobile Action Toolbar */
        .ld-toolbar { 
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important; 
            padding: 10px 14px !important; 
            min-height: auto !important;
        }
        .ld-toolbar > div { 
            width: auto !important; 
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            flex-wrap: wrap !important; 
        }
        .ld-toolbar .ld-head-actions {
            margin-left: auto !important;
            display: flex !important;
            flex-wrap: wrap !important;
            max-width: 100% !important;
            align-items: center !important;
            gap: 6px !important;
        }
        .ld-tool {
            height: 32px !important;
            padding: 0 10px !important;
            font-size: 12px !important;
            border-radius: 8px !important;
            background: var(--ld-card) !important;
            border: 1px solid var(--ld-input) !important;
        }
    }

    .ld-tool { display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 10px; border-radius: 6px; font-size: 13px; color: var(--ld-text); background: transparent; border: 0; cursor: pointer; white-space: nowrap !important; flex-shrink: 0 !important; }
    .ld-tool:hover, .ld-tool[aria-expanded="true"] { background: var(--ld-grey-btn); }
    .ld-tool.is-on { background: var(--ld-accent-soft); color: var(--ld-accent); }
    .ld-tool svg { width: 16px; height: 16px; flex-shrink: 0; }
    .ld-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; color: var(--ld-muted); flex-shrink: 0 !important; }
    .ld-icon-btn:hover { background: var(--ld-grey-btn); color: var(--ld-text); }
    .ld-icon-btn.is-on { background: var(--ld-accent-soft); color: var(--ld-accent); }
    .ld-icon-btn svg { width: 18px; height: 18px; }
    .ld-sep { width: 1px; height: 22px; background: var(--ld-border); margin: 0 4px; }

    .ld-accent { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; min-height: 38px !important; height: auto !important; padding: 8px 16px !important; background: var(--ld-accent) !important; color: #fff !important; font-size: 13px !important; font-weight: 700 !important; border: 1px solid var(--ld-accent-edge) !important; border-radius: 12px !important; text-decoration: none !important; cursor: pointer !important; white-space: nowrap !important; flex-shrink: 0 !important; line-height: 1.25 !important; }
    .ld-accent:hover { background: var(--ld-accent-hover) !important; }
    /* an id outranks the app-wide rule that paints every filter-form submit button blue */
    #ld-apply { background: var(--ld-accent) !important; border-color: var(--ld-accent-edge) !important; color: #fff !important; }
    #ld-apply:hover { background: var(--ld-accent-hover) !important; }
    .ld-split { --control-height: 38px; --radius-control: 12px; display: inline-flex; height: 38px; min-height: 38px; border: 1px solid var(--ld-accent-edge); border-radius: 12px; overflow: hidden; background: var(--ld-accent); }
    .ld-split > a, .ld-split > button { display: inline-flex; align-items: center; justify-content: center; height: 100% !important; min-height: 38px !important; margin: 0; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; background: var(--ld-accent) !important; color: #fff !important; font-size: 13px; font-weight: 700; text-decoration: none !important; cursor: pointer; }
    .ld-split > a { padding: 0 16px; }
    .ld-split > button { width: 34px; justify-content: center; padding: 0 !important; border-left: 1px solid rgba(255, 255, 255, .35) !important; }
    .ld-split > a:hover, .ld-split > button:hover, .ld-split > button[aria-expanded="true"] { background: var(--ld-accent-hover) !important; }
    .ld-outline { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; min-height: 38px !important; height: auto !important; min-width: 38px; padding: 8px 14px !important; background: var(--ld-card) !important; color: var(--ld-text) !important; border: 1px solid var(--ld-input) !important; border-radius: 12px !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; text-decoration: none !important; white-space: nowrap !important; flex-shrink: 0 !important; line-height: 1.25 !important; }
    .ld-outline:hover { background: var(--ld-hover) !important; }

    .ld-menu { position: absolute; z-index: 50; min-width: 190px; max-width: calc(100vw - 32px); margin-top: 6px; padding: 6px 0; background: var(--ld-card); border: 1px solid var(--ld-border); border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1); }
    @media (max-width: 768px) {
        .ld-menu { 
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            right: auto !important;
            bottom: auto !important;
            transform: translate(-50%, -50%) !important;
            width: calc(100vw - 48px) !important;
            max-width: 320px !important;
            z-index: 99999 !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.15), 0 0 0 100vmax rgba(15, 23, 42, 0.45) !important;
            border-radius: 16px !important;
            padding: 8px 0 !important;
        }
        .ld-menu.left-0, .ld-menu.right-0 { 
            left: 50% !important; 
            right: auto !important; 
        }
    }
    .ld-menu a, .ld-menu button, .ld-menu label { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 10px; padding: 7px 12px; font-size: 14px; color: var(--ld-text) !important; text-align: left; text-decoration: none !important; background: transparent; border: 0; cursor: pointer; }
    .ld-menu a:hover, .ld-menu button:hover, .ld-menu label:hover { background: var(--ld-hover); }
    /* the app-wide button rule (five :not() filters, app.css) centres button text; the repeated
       class outranks it so menu items line up on the left like the links */
    .ld-root .ld-menu.ld-menu.ld-menu.ld-menu.ld-menu button { display: flex !important; justify-content: flex-start !important; text-align: left !important; font-weight: 400; }
    .ld-menu .is-current { font-weight: 600; }
    .ld-menu hr { border: 0; border-top: 1px solid var(--ld-line); margin: 4px 0; }

    .ld-body { display: flex; gap: 12px; padding: 12px; align-items: stretch; }
    .ld-card { background: var(--ld-card); border: 1px solid var(--ld-border); border-radius: 6px; }

    .ld-filter { width: 202px; flex-shrink: 0; display: flex; flex-direction: column; min-height: 0; }
    .ld-filter-scroll { flex: 1; overflow-y: auto; padding: 16px 18px; }
    .ld-filter h2 { font-size: 14px; font-weight: 700; margin-bottom: 14px; color: var(--ld-text); }
    .ld-filter summary { list-style: none; display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 14px; cursor: pointer; margin: 16px 0 10px; color: var(--ld-text); }
    .ld-filter summary::-webkit-details-marker { display: none; }
    .ld-filter summary .ld-caret { flex-shrink: 0; transition: transform .15s; }
    .ld-filter summary span:not(.ld-caret) { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    /* solid triangles drawn with borders, so no svg fill rule can hollow them out */
    .ld-caret { display: inline-block; width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid currentColor; }
    .ld-caret-sm { border-left-width: 4px; border-right-width: 4px; border-top-width: 5px; }
    .ld-filter details:not([open]) summary .ld-caret { transform: rotate(-90deg); }
    .ld-filter .ld-sub { font-size: 12px; color: var(--ld-muted); margin: 10px 0 6px; }
    /* the app sets every label to display: block; these stay checkbox-then-text, with wrapped text indented */
    .ld-root label.ld-check, .ld-root .ld-menu label { display: flex !important; }
    .ld-check > span:not(.ld-count) { flex: 1; min-width: 0; }
    .ld-check { display: flex; align-items: flex-start; gap: 8px; padding: 4px 0; font-size: 14px; color: var(--ld-text); cursor: pointer; }
    .ld-check input { width: 14px; height: 14px; margin-top: 3px; flex-shrink: 0; border: 1px solid #9aa3af; border-radius: 2px; accent-color: var(--ld-accent); }
    .ld-check .ld-count { margin-left: auto; color: var(--ld-muted); font-size: 12px; }
    .ld-input { width: 100%; height: 32px; padding: 0 10px; border: 1px solid var(--ld-input) !important; border-radius: 4px; background-color: var(--ld-card) !important; color: var(--ld-text) !important; font-size: 14px; }
    .ld-root .ld-filter .ld-input { width: 100% !important; min-width: 0 !important; max-width: 100% !important; }
    select.ld-input, .ld-foot select { background-repeat: no-repeat !important; background-position: right 8px center !important; background-size: 10px 10px !important; padding-right: 26px !important; font-weight: 400 !important; }
    .ld-filter-actions { display: flex; gap: 8px; padding: 10px 18px; border-top: 1px solid var(--ld-line); }

    .ld-main { flex: 1; min-width: 0; display: flex; flex-direction: column; min-height: 0; }
    .ld-scroll {
        flex: 1 1 0%;
        overflow-y: auto;
        overflow-x: auto;
        min-height: 0;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #94a3b8 #f1f5f9;
    }
    html.dark .ld-scroll {
        scrollbar-color: #475569 #0f172a;
    }
    .ld-scroll::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    .ld-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    html.dark .ld-scroll::-webkit-scrollbar-track {
        background: #0f172a;
    }
    .ld-scroll::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 6px;
        border: 2px solid #f1f5f9;
    }
    html.dark .ld-scroll::-webkit-scrollbar-thumb {
        background: #475569;
        border: 2px solid #0f172a;
    }
    .ld-scroll::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
    html.dark .ld-scroll::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
    .ld-scroll::-webkit-scrollbar-button:single-button:vertical:decrement {
        height: 14px;
        width: 10px;
        background-color: #e2e8f0;
        background-position: center center;
        background-repeat: no-repeat;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 24 24' fill='none' stroke='%23334155' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='18 15 12 9 6 15'></polyline></svg>");
        border-radius: 3px 3px 0 0;
        display: block;
    }
    .ld-scroll::-webkit-scrollbar-button:single-button:vertical:decrement:hover {
        background-color: #cbd5e1;
    }
    .ld-scroll::-webkit-scrollbar-button:single-button:vertical:increment {
        height: 14px;
        width: 10px;
        background-color: #e2e8f0;
        background-position: center center;
        background-repeat: no-repeat;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 24 24' fill='none' stroke='%23334155' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>");
        border-radius: 0 0 3px 3px;
        display: block;
    }
    .ld-scroll::-webkit-scrollbar-button:single-button:vertical:increment:hover {
        background-color: #cbd5e1;
    }
    html.dark .ld-scroll::-webkit-scrollbar-button:single-button:vertical:decrement {
        background-color: #1e293b;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='18 15 12 9 6 15'></polyline></svg>");
    }
    html.dark .ld-scroll::-webkit-scrollbar-button:single-button:vertical:increment {
        background-color: #1e293b;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>");
    }
    .ld-root .ld-scroll table.ld-table { width: 100%; border: 0 !important; border-collapse: separate !important; border-spacing: 0 !important; }
    .ld-root .ld-scroll table.ld-table thead, .ld-root .ld-scroll table.ld-table thead tr { background: transparent !important; border: 0 !important; }
    .ld-root .ld-scroll table.ld-table th { position: sticky; top: 0; z-index: 10; background: var(--ld-card) !important; height: 38px; padding: 0 12px; text-align: left; font-size: 13px; font-weight: 600; color: var(--ld-text); white-space: nowrap; border: 0 !important; box-shadow: inset 0 -1px 0 var(--ld-border), inset -1px 0 0 var(--ld-line); }
    .ld-root .ld-scroll table.ld-table th:last-child { box-shadow: inset 0 -1px 0 var(--ld-border); }
    .ld-root .ld-scroll table.ld-table td { padding: 9px 12px; font-size: 13.5px; color: var(--ld-text); vertical-align: middle; line-height: 1.4; border: 0 !important; border-bottom: 1px solid var(--ld-line) !important; background: var(--ld-card) !important; }
    .ld-root .ld-scroll table.ld-table tbody tr:hover td { background: var(--ld-hover) !important; }
    .ld-root .ld-scroll table.ld-table tbody tr.is-picked td { background: var(--ld-accent-soft) !important; }
    .ld-root .ld-scroll table.ld-table .ld-box { width: 44px; min-width: 44px !important; padding-left: 14px; padding-right: 0; }
    .ld-root .ld-scroll table.ld-table .ld-box input { width: 14px; height: 14px; border: 1px solid #9aa3af; border-radius: 2px; accent-color: var(--ld-accent); cursor: pointer; }
    .ld-root .ld-scroll table.ld-table td a.ld-name { color: var(--link-color) !important; font-size: 13.5px !important; font-weight: 600 !important; }
    .ld-root .ld-scroll table.ld-table td a.ld-name:hover { color: var(--link-hover) !important; text-decoration: underline !important; }
    .ld-root .ld-wrap { overflow-wrap: anywhere; max-width: 240px; }
    .ld-root .ld-row-actions { display: flex; justify-content: flex-end; gap: 2px; opacity: 0; }
    .ld-root tr:hover .ld-row-actions, .ld-root .ld-row-actions:focus-within { opacity: 1; }
    @media (hover: none) { .ld-root .ld-row-actions { opacity: 1; } }
    .ld-root .ld-scroll table.ld-table td .ld-row-actions a, .ld-root .ld-row-actions button { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 4px; color: var(--ld-muted) !important; background: transparent !important; border: 0 !important; cursor: pointer; }
    .ld-root .ld-scroll table.ld-table td .ld-row-actions a:hover, .ld-root .ld-row-actions button:hover { background: var(--ld-grey-btn) !important; color: var(--ld-text) !important; }

    .ld-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid var(--ld-border); font-size: 14px; flex-shrink: 0; background: var(--ld-card); position: relative; z-index: 10; }
    .ld-foot select { height: 28px; padding: 0 26px 0 8px; border: 1px solid var(--ld-input) !important; border-radius: 4px; background-color: var(--ld-card) !important; color: var(--ld-text) !important; font-size: 13px; }
    .ld-page-arrow { display: inline-flex; width: 26px; height: 26px; align-items: center; justify-content: center; border-radius: 4px; color: var(--ld-muted) !important; }
    a.ld-page-arrow:hover { background: var(--ld-grey-btn); color: var(--ld-text) !important; }
    span.ld-page-arrow { opacity: .35; }

    .ld-board { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 4px; flex: 1; min-height: 0; }
    .ld-lane { width: 260px; flex-shrink: 0; display: flex; flex-direction: column; background: var(--ld-card); border: 1px solid var(--ld-border); border-radius: 6px; min-height: 0; }
    .ld-lane h3 { display: flex; justify-content: space-between; padding: 10px 12px; font-size: 14px; font-weight: 600; border-bottom: 1px solid var(--ld-line); }
    .ld-lane-list { padding: 8px; display: flex; flex-direction: column; gap: 8px; overflow-y: auto; }
    .ld-card-item { display: block; padding: 10px 12px; border: 1px solid var(--ld-line); border-radius: 4px; background: var(--ld-card); color: var(--ld-text) !important; text-decoration: none !important; }
    .ld-card-item:hover { border-color: var(--ld-input); background: var(--ld-hover); }

    .ld-letters { display: grid; grid-template-columns: repeat(6, 1fr); gap: 2px; padding: 6px; width: 228px; }
    .ld-letters a { justify-content: center !important; padding: 6px 0 !important; border-radius: 4px; }
    .ld-letters a.is-current { background: var(--ld-accent-soft); font-weight: 600; }

    /* Ticked rows (list-pick) and the mass action dialogs (list-mass-actions) */
    .ld-root .ld-scroll table.ld-table tbody tr:has(.ld-box input:checked) td { background: var(--ld-accent-soft) !important; }
    .ld-danger { color: var(--status-danger-fg) !important; }
    html.dark .ld-danger { color: #fca5a5 !important; }
    .ld-root .ld-danger-fill { background: #dc2626 !important; border-color: #b91c1c !important; }
    .ld-root .ld-danger-fill:hover { background: #b91c1c !important; }
    .ld-dialog-wrap { position: fixed; inset: 0; z-index: 80; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .ld-dialog-backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, .45); }
    html.dark .ld-dialog-backdrop { background: rgba(0, 0, 0, .6); }
    .ld-dialog { position: relative; width: 100%; max-width: 440px; background: var(--ld-card); color: var(--ld-text); border: 1px solid var(--ld-border); border-radius: 8px; padding: 20px; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); }
    .ld-dialog h2 { font-size: 16px; font-weight: 700; margin-bottom: 10px; }
    .ld-dialog p { font-size: 14px; color: var(--ld-muted); margin-bottom: 8px; }
    .ld-dialog p strong { color: var(--ld-text); }
    .ld-dialog .ld-sub { display: block; font-size: 12px; color: var(--ld-muted); margin: 12px 0 6px; }
    .ld-dialog-actions { display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap; }
    .ld-dialog-actions [disabled] { opacity: .5; cursor: not-allowed; }

    /* Print View: just the list, on plain paper */
    @media print {
        aside, .crm-sidebar-container, .crm-topbar, nav.fixed, .ld-above, .ld-views, .ld-toolbar, .ld-filter, .ld-foot form, .ld-page-arrow,
        .ld-box, .ld-row-actions, .ld-cols, .ld-dialog-wrap, [x-cloak] { display: none !important; }
        html, body, .crm-app-container, .crm-app-container > div, #main-content { height: auto !important; min-height: 0 !important; overflow: visible !important; background: #fff !important; }
        #main-content { padding: 0 !important; }
        .ld-root, .ld-band, .ld-card { background: #fff !important; border-color: #ccc !important; }
        .ld-body { display: block !important; height: auto !important; padding: 0 !important; }
        .ld-scroll { overflow: visible !important; }
        .ld-root .ld-scroll table.ld-table th { position: static; }
        .ld-root .ld-scroll table.ld-table td, .ld-root .ld-scroll table.ld-table th { padding: 6px 8px; font-size: 11px; color: #000 !important; background: #fff !important; }
        .ld-root .ld-scroll table.ld-table tr { break-inside: avoid; }
    }
</style>

<script>
    // The filter panel starts closed and opens from the Filter button; it is open on load only
    // when filters are applied (filtersOn), so the applied values stay in view after Apply Filter.
    if (!window.listPage) window.listPage = (pageIds, key, columns = {}, filtersOn = false) => ({
        showFilter: filtersOn,
        selected: [],
        pageIds,
        menu: null,
        filterQuery: '',
        cols: { ...columns },
        init() {
            try { Object.assign(this.cols, JSON.parse(localStorage.getItem(key + '.columns') || '{}')); } catch (e) {}
            this.$watch('cols', (v) => { try { localStorage.setItem(key + '.columns', JSON.stringify(v)); } catch (e) {} });
            this.$nextTick(() => this.fit());
            window.addEventListener('resize', () => this.fit());
        },
        // On a desktop the panels fill the window and scroll inside, so the footer stays in view
        fit() {
            const body = this.$refs.body;
            if (!body) return;
            if (window.innerWidth < 1024) {
                body.style.height = '';
                body.style.maxHeight = '';
                return;
            }
            const top = body.getBoundingClientRect().top;
            const available = Math.max(380, window.innerHeight - top - 24);
            const table = body.querySelector('table.ld-table');
            const rowCount = table ? table.querySelectorAll('tbody tr').length : 0;

            // When there are few records (<= 6 rows) and the filter drawer is closed,
            // let the card hug its content naturally without creating huge empty blank voids!
            if (rowCount <= 6 && !this.showFilter) {
                body.style.height = '';
                body.style.maxHeight = available + 'px';
            } else {
                body.style.height = available + 'px';
                body.style.maxHeight = '';
            }
        },
        get allPicked() { return this.pageIds.length > 0 && this.pageIds.every((id) => this.selected.includes(id)); },
        toggleAll() { this.selected = this.allPicked ? [] : [...this.pageIds]; },
        open(name) { this.menu = this.menu === name ? null : name; },
        shows(label) { return label.toLowerCase().includes(this.filterQuery.trim().toLowerCase()); },
        scrollUp() {
            const scroll = this.$el.querySelector('.ld-scroll') || document.querySelector('.ld-scroll');
            if (scroll) scroll.scrollBy({ top: -240, behavior: 'smooth' });
        },
        scrollDown() {
            const scroll = this.$el.querySelector('.ld-scroll') || document.querySelector('.ld-scroll');
            if (scroll) scroll.scrollBy({ top: 240, behavior: 'smooth' });
        },
        scrollToTop() {
            const scroll = this.$el.querySelector('.ld-scroll') || document.querySelector('.ld-scroll');
            if (scroll) scroll.scrollTo({ top: 0, behavior: 'smooth' });
        },
        scrollToBottom() {
            const scroll = this.$el.querySelector('.ld-scroll') || document.querySelector('.ld-scroll');
            if (scroll) scroll.scrollTo({ top: scroll.scrollHeight, behavior: 'smooth' });
        },
    });

    // The mass delete, update and email dialogs; `selected` comes from the listPage() around them
    if (!window.listMass) window.listMass = (fields, emailsUrl) => ({
        dialog: null,
        fields,
        field: fields.length ? fields[0].key : '',
        value: '',
        emails: [],
        missing: 0,
        loading: false,
        copied: false,
        options() { return (this.fields.find((f) => f.key === this.field) || { options: [] }).options; },
        open(kind, ids) {
            this.dialog = kind;
            this.copied = false;
            if (kind === 'email' && ids.length) this.loadEmails(ids);
        },
        async loadEmails(ids) {
            this.loading = true;
            try {
                const res = await fetch(emailsUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ ids }),
                });
                const data = await res.json();
                this.emails = data.emails || [];
                this.missing = data.missing || 0;
            } catch (e) {
                this.emails = [];
                this.missing = ids.length;
            }
            this.loading = false;
        },
    });
</script>
