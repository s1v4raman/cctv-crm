import Alpine from 'alpinejs';
import QRCode from 'qrcode';
import './a11y';
import './responsive-tables';
import './filters';
import formSteps from './form-steps';
import hardwareScannerModalComponent, { parseDeviceBarcode } from './hardware-scanner';
import './crm-searchable-select';

window.Alpine = Alpine;
window.QRCode = QRCode;
window.parseDeviceBarcode = parseDeviceBarcode;
Alpine.data('formSteps', formSteps);
Alpine.data('hardwareScannerModalComponent', hardwareScannerModalComponent);

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
