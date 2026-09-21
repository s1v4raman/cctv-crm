import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.css';

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

Alpine.start();
