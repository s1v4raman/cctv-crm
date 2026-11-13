// Phone layout for data tables: rows of <table class="crm-responsive"> turn into
// labelled cards (styles in app.css). Each cell gets data-label from its column
// header, including rows added later by Alpine or scripts (e.g. quotation items).

const headerLabels = (table) => {
    const labels = [];
    table.querySelectorAll('thead tr:last-child th').forEach((th) => {
        // Skip checkbox and column chooser / action column headers
        if (th.classList.contains('ld-box') || th.classList.contains('ld-cols') || th.querySelector('.ld-menu')) {
            for (let i = 0; i < (th.colSpan || 1); i++) labels.push('');
            return;
        }
        // Clone th and remove interactive menus, buttons, inputs, dropdowns
        const clone = th.cloneNode(true);
        clone.querySelectorAll('.ld-menu, .dropdown, select, input, button, svg').forEach((el) => el.remove());
        const text = clone.textContent.trim().replace(/\s+/g, ' ');
        for (let i = 0; i < (th.colSpan || 1); i++) labels.push(text);
    });
    return labels;
};

const wrapMultiValue = (cell) => {
    if (cell.classList.contains('ld-box') || cell.hasAttribute('data-card-header') || cell.classList.contains('crm-card-header-cell') || (cell.colSpan || 1) > 1) return;
    if (cell.querySelector('.ld-row-actions') || cell.querySelector('form button') || cell.classList.contains('ld-actions-cell')) return;
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
        const cells = [...row.children].filter((el) => el.tagName === 'TD');
        const totalCols = cells.length;
        const firstNonBoxIdx = cells.length > 0 && cells[0].classList.contains('ld-box') ? 1 : 0;

        cells.forEach((cell, idx) => {
            const rawLabel = (cell.colSpan || 1) > 1 ? '' : (labels[column] || '');
            const isActionCol = rawLabel.toLowerCase().includes('action') || 
                                cell.classList.contains('ld-actions-cell') ||
                                cell.classList.contains('crm-card-actions') ||
                                cell.hasAttribute('data-card-actions') ||
                                cell.getAttribute('data-col') === 'actions' ||
                                cell.querySelector('.ld-row-actions') ||
                                (idx === totalCols - 1 && cell.querySelector('button, form, a[href*="edit"], a[href*="delete"]'));
            
            const isHeaderCol = cell.hasAttribute('data-card-header') || idx === firstNonBoxIdx;

            if (isActionCol) {
                cell.setAttribute('data-card-actions', 'true');
                cell.setAttribute('data-label', '');
            } else if (isHeaderCol) {
                cell.setAttribute('data-card-header', 'true');
                cell.classList.add('crm-card-header-cell');
                cell.setAttribute('data-label', '');
            } else if (!cell.hasAttribute('data-label')) {
                cell.setAttribute('data-label', rawLabel);
            }

            if (table.classList.contains('crm-auto-cards') || table.classList.contains('crm-responsive') || table.classList.contains('ld-table')) {
                wrapMultiValue(cell);
            }
            column += cell.colSpan || 1;
        });
    });
};

const shouldAutoCard = (table) => {
    if (table.classList.contains('no-mobile-cards') || table.classList.contains('no-auto-cards') || table.hasAttribute('data-dense')) return false;
    if (table.closest('.hidden.md\\:block, .md\\:table, .md\\:hidden')) return false;
    if (!table.querySelector('thead') || !table.querySelector('tbody')) return false;
    // Skip very wide matrix grids (e.g. 31-day attendance month view)
    const thCount = table.querySelectorAll('thead tr:last-child th').length;
    if (thCount === 0 || thCount > 12) return false;
    return true;
};

const initResponsiveTables = (root = document) => {
    const tables = root.querySelectorAll('table.crm-responsive, table.crm-auto-cards, .crm-content table.ld-table, .crm-content .overflow-x-auto > table, .overflow-x-auto > table:not(.no-auto-cards)');
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
