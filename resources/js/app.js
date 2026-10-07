import Alpine from 'alpinejs';
import TomSelect from 'tom-select';

window.Alpine = Alpine;
window.TomSelect = TomSelect;

window.initSearchableSelect = function(element, options = {}) {
    if (!element || element.tomselect) return element.tomselect;
    
    // Don't initialize if marked to exclude
    if (element.hasAttribute('data-no-tom')) return null;

    try {
        const placeholder = element.getAttribute('data-placeholder') || 
                            element.querySelector('option[value=""]')?.textContent || 
                            'Type to search...';

        const ts = new TomSelect(element, {
            create: false,
            maxOptions: 150,
            allowEmptyOption: true,
            placeholder: placeholder,
            dropdownParent: 'body',
            plugins: ['clear_button'],
            onChange: function(val) {
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change', { bubbles: true }));
            },
            ...options
        });
        return ts;
    } catch (e) {
        console.warn('TomSelect init failed on', element, e);
        return null;
    }
};

window.initAllSearchableSelects = function(root = document) {
    const selectors = [
        'select.searchable-select',
        'select[data-searchable]',
        'select[name="lead_id"]:not([data-no-tom])',
        'select[name="installation_job_id"]:not([data-no-tom])',
        'select[name="product_id"]:not([data-no-tom])'
    ];

    root.querySelectorAll(selectors.join(', ')).forEach(el => {
        window.initSearchableSelect(el);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initAllSearchableSelects();
});

if (!window.AlpineStarted) {
    window.AlpineStarted = true;
    try {
        if (!Alpine.store('sidebar')) {
            Alpine.store('sidebar', {
                collapsed: localStorage.getItem('crm_sidebar_collapsed') === 'true',
                toggle() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('crm_sidebar_collapsed', this.collapsed ? 'true' : 'false');
                    window.dispatchEvent(new CustomEvent('sidebar-collapsed-changed', { detail: { collapsed: this.collapsed } }));
                }
            });
        }
        if (!Alpine.store('shortcuts')) {
            Alpine.store('shortcuts', {
                open: false,
                toggle() {
                    this.open = !this.open;
                    window.dispatchEvent(new CustomEvent('shortcuts-toggle', { detail: { open: this.open } }));
                }
            });
        }
        Alpine.start();
    } catch (e) {
        // Alpine already initialized or started
    }
}
