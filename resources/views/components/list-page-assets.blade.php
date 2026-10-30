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

    .ld-tool { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 9px; border-radius: 4px; font-size: 14px; color: var(--ld-text); background: transparent; border: 0; cursor: pointer; }
    .ld-tool:hover, .ld-tool[aria-expanded="true"] { background: var(--ld-grey-btn); }
    .ld-tool.is-on { background: var(--ld-accent-soft); color: var(--ld-accent); }
    .ld-tool svg { width: 16px; height: 16px; flex-shrink: 0; }
    .ld-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 4px; color: var(--ld-muted); }
    .ld-icon-btn:hover { background: var(--ld-grey-btn); color: var(--ld-text); }
    .ld-icon-btn.is-on { background: var(--ld-accent-soft); color: var(--ld-accent); }
    .ld-icon-btn svg { width: 18px; height: 18px; }
    .ld-sep { width: 1px; height: 22px; background: var(--ld-border); margin: 0 4px; }

    .ld-accent { display: inline-flex; align-items: center; height: 32px; padding: 0 14px; background: var(--ld-accent) !important; color: #fff !important; font-size: 14px; font-weight: 600; border: 1px solid var(--ld-accent-edge) !important; text-decoration: none !important; cursor: pointer; }
    .ld-accent:hover { background: var(--ld-accent-hover) !important; }
    /* an id outranks the app-wide rule that paints every filter-form submit button blue */
    #ld-apply { background: var(--ld-accent) !important; border-color: var(--ld-accent-edge) !important; color: #fff !important; }
    #ld-apply:hover { background: var(--ld-accent-hover) !important; }
    .ld-split { --control-height: 30px; --radius-control: 0px; display: inline-flex; height: 32px; border: 1px solid var(--ld-accent-edge); border-radius: 4px; overflow: hidden; background: var(--ld-accent); }
    .ld-split > a, .ld-split > button { display: inline-flex; align-items: center; height: 100% !important; margin: 0; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; background: var(--ld-accent) !important; color: #fff !important; font-size: 14px; font-weight: 600; text-decoration: none !important; cursor: pointer; }
    .ld-split > a { padding: 0 14px; }
    .ld-split > button { width: 30px; justify-content: center; padding: 0 !important; border-left: 1px solid rgba(255, 255, 255, .35) !important; }
    .ld-split > a:hover, .ld-split > button:hover, .ld-split > button[aria-expanded="true"] { background: var(--ld-accent-hover) !important; }
    .ld-outline { display: inline-flex; align-items: center; justify-content: center; height: 32px; min-width: 40px; padding: 0 10px; background: var(--ld-card) !important; color: var(--ld-text) !important; border: 1px solid var(--ld-input) !important; border-radius: 4px; font-size: 14px; cursor: pointer; text-decoration: none !important; }
    .ld-outline:hover { background: var(--ld-hover) !important; }

    .ld-menu { position: absolute; z-index: 40; min-width: 190px; margin-top: 4px; padding: 4px 0; background: var(--ld-card); border: 1px solid var(--ld-border); border-radius: 6px; box-shadow: 0 8px 24px rgba(15, 23, 42, .12); }
    .ld-menu a, .ld-menu button, .ld-menu label { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 10px; padding: 7px 12px; font-size: 14px; color: var(--ld-text) !important; text-align: left; text-decoration: none !important; background: transparent; border: 0; cursor: pointer; }
    .ld-menu a:hover, .ld-menu button:hover, .ld-menu label:hover { background: var(--ld-hover); }
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
    .ld-scroll { flex: 1; overflow: auto; min-height: 0; }
    .ld-root .ld-scroll table.ld-table { width: 100%; border: 0 !important; border-collapse: separate !important; border-spacing: 0 !important; }
    .ld-root .ld-scroll table.ld-table thead, .ld-root .ld-scroll table.ld-table thead tr { background: transparent !important; border: 0 !important; }
    .ld-root .ld-scroll table.ld-table th { position: sticky; top: 0; z-index: 2; background: var(--ld-card) !important; height: 40px; padding: 0 14px; text-align: left; font-size: 14px; font-weight: 500; color: var(--ld-text); white-space: nowrap; border: 0 !important; box-shadow: inset 0 -1px 0 var(--ld-border), inset -1px 0 0 var(--ld-line); }
    .ld-root .ld-scroll table.ld-table th:last-child { box-shadow: inset 0 -1px 0 var(--ld-border); }
    .ld-root .ld-scroll table.ld-table td { padding: 14px; font-size: 14px; color: var(--ld-text); vertical-align: top; border: 0 !important; border-bottom: 1px solid var(--ld-line) !important; background: var(--ld-card) !important; }
    .ld-root .ld-scroll table.ld-table tbody tr:hover td { background: var(--ld-hover) !important; }
    .ld-root .ld-scroll table.ld-table tbody tr.is-picked td { background: var(--ld-accent-soft) !important; }
    .ld-root .ld-scroll table.ld-table .ld-box { width: 48px; min-width: 48px !important; padding-left: 18px; padding-right: 0; }
    .ld-root .ld-scroll table.ld-table .ld-box input { width: 14px; height: 14px; border: 1px solid #9aa3af; border-radius: 2px; accent-color: var(--ld-accent); cursor: pointer; }
    .ld-root .ld-scroll table.ld-table td a.ld-name { color: var(--ld-accent, var(--crm-accent, #2563eb)) !important; font-size: 14px !important; font-weight: 600 !important; }
    .ld-root .ld-scroll table.ld-table td a.ld-name:hover { color: var(--ld-accent-hover, var(--crm-accent-hover, #1d4ed8)) !important; text-decoration: underline !important; }
    .ld-root .ld-wrap { overflow-wrap: anywhere; max-width: 240px; }
    .ld-root .ld-row-actions { display: flex; justify-content: flex-end; gap: 2px; opacity: 0; }
    .ld-root tr:hover .ld-row-actions, .ld-root .ld-row-actions:focus-within { opacity: 1; }
    @media (hover: none) { .ld-root .ld-row-actions { opacity: 1; } }
    .ld-root .ld-scroll table.ld-table td .ld-row-actions a, .ld-root .ld-row-actions button { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 4px; color: var(--ld-muted) !important; background: transparent !important; border: 0 !important; cursor: pointer; }
    .ld-root .ld-scroll table.ld-table td .ld-row-actions a:hover, .ld-root .ld-row-actions button:hover { background: var(--ld-grey-btn) !important; color: var(--ld-text) !important; }

    .ld-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid var(--ld-border); font-size: 14px; }
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
            body.style.height = window.innerWidth < 1024 ? '' : Math.max(420, window.innerHeight - body.getBoundingClientRect().top) + 'px';
        },
        get allPicked() { return this.pageIds.length > 0 && this.pageIds.every((id) => this.selected.includes(id)); },
        toggleAll() { this.selected = this.allPicked ? [] : [...this.pageIds]; },
        open(name) { this.menu = this.menu === name ? null : name; },
        shows(label) { return label.toLowerCase().includes(this.filterQuery.trim().toLowerCase()); },
    });
</script>
