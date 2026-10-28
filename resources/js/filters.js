// List filters: <form data-auto-submit> re-runs the search as soon as a select (or date)
// changes, so status filters apply without pressing "Filter". The short delay lets
// keyboard users arrow through options; focus returns to the same field after reload.

const REFOCUS_KEY = 'crm_filter_refocus';
let pending = null;

document.addEventListener('change', (event) => {
    const field = event.target;
    const form = field.form;
    if (!form || !form.hasAttribute('data-auto-submit') || !field.matches('select, input[type="date"]')) return;
    clearTimeout(pending);
    pending = setTimeout(() => {
        try {
            if (field.name) sessionStorage.setItem(REFOCUS_KEY, field.name);
        } catch (e) {}
        form.requestSubmit();
    }, 450);
});

const restoreFocus = () => {
    let name = null;
    try {
        name = sessionStorage.getItem(REFOCUS_KEY);
        sessionStorage.removeItem(REFOCUS_KEY);
    } catch (e) {}
    if (!name) return;
    const field = document.querySelector(`form[data-auto-submit] [name="${CSS.escape(name)}"]`);
    field?.focus({ preventScroll: true });
};

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', restoreFocus);
else restoreFocus();
