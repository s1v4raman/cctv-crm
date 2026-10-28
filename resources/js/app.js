import Alpine from 'alpinejs';
import './a11y';
import './responsive-tables';

window.Alpine = Alpine;

// Clean dropdown design: Native .crm-select is used across all modules,
// avoiding nested box-in-box wrappers that clip text.
window.initSearchableSelect = function(element, options = {}) {
    return null;
};

window.initAllSearchableSelects = function(root = document) {
    // Native .crm-select handles dropdown presentation cleanly
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
