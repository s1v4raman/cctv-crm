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

const wrapMultiValue = (cell) => {
    if (cell.classList.contains('ld-box') || (cell.colSpan || 1) > 1) return;
    if (cell.firstElementChild?.classList?.contains('crm-card-val')) return;
    const meaningful = [...cell.childNodes].filter(
        (n) => n.nodeType === 1 || (n.nodeType === 3 && n.textContent.trim() !== '')
    );
    if (meaningful.length <= 1) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'crm-card-val';
    while (cell.firstChild) wrapper.appendChild(cell.firstChild);
    cell.appendChild(wrapper);
};

const labelRows = (table) => {
    const labels = headerLabels(table);
    table.querySelectorAll('tbody > tr').forEach((row) => {
        let column = 0;
        [...row.children].forEach((cell) => {
            if (cell.tagName === 'TD') {
                if (!cell.hasAttribute('data-label')) {
                    const rawLabel = (cell.colSpan || 1) > 1 ? '' : (labels[column] || '');
                    cell.setAttribute('data-label', rawLabel);
                }
                if (table.classList.contains('crm-auto-cards') || table.classList.contains('crm-responsive')) {
                    wrapMultiValue(cell);
                }
            }
            column += cell.colSpan || 1;
        });
    });
};

const shouldAutoCard = (table) => {
    if (table.classList.contains('no-mobile-cards') || table.hasAttribute('data-dense')) return false;
    if (!table.querySelector('thead') || !table.querySelector('tbody')) return false;
    // Skip very wide matrix grids (e.g. 31-day attendance month view)
    const thCount = table.querySelectorAll('thead tr:last-child th').length;
    if (thCount === 0 || thCount > 12) return false;
    return true;
};

const initResponsiveTables = (root = document) => {
    const tables = root.querySelectorAll('table.crm-responsive, .crm-content table.ld-table, .crm-content .overflow-x-auto > table');
    tables.forEach((table) => {
        if (!table.classList.contains('crm-responsive') && shouldAutoCard(table)) {
            table.classList.add('crm-auto-cards');
        }
        if (!table.classList.contains('crm-responsive') && !table.classList.contains('crm-auto-cards')) return;
        labelRows(table);
        if (table._crmResponsiveObserved) return;
        table._crmResponsiveObserved = true;
        new MutationObserver(() => labelRows(table)).observe(table, { childList: true, subtree: true });
    });
};

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initResponsiveTables());
else initResponsiveTables();
