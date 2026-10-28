// Phone layout for data tables: rows of <table class="crm-responsive"> turn into
// labelled cards (styles in app.css). Each cell gets data-label from its column
// header, including rows added later by Alpine or scripts (e.g. quotation items).

const headerLabels = (table) => {
    const labels = [];
    table.querySelectorAll('thead tr:last-child th').forEach((th) => {
        const text = th.textContent.trim().replace(/\s+/g, ' ');
        for (let i = 0; i < (th.colSpan || 1); i++) labels.push(text);
    });
    return labels;
};

const labelRows = (table) => {
    const labels = headerLabels(table);
    table.querySelectorAll('tbody > tr').forEach((row) => {
        let column = 0;
        [...row.children].forEach((cell) => {
            if (cell.tagName === 'TD' && !cell.hasAttribute('data-label')) {
                cell.setAttribute('data-label', (cell.colSpan || 1) > 1 ? '' : (labels[column] || ''));
            }
            column += cell.colSpan || 1;
        });
    });
};

const initResponsiveTables = (root = document) => {
    root.querySelectorAll('table.crm-responsive').forEach((table) => {
        labelRows(table);
        if (table._crmResponsiveObserved) return;
        table._crmResponsiveObserved = true;
        new MutationObserver(() => labelRows(table)).observe(table, { childList: true, subtree: true });
    });
};

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initResponsiveTables());
else initResponsiveTables();
