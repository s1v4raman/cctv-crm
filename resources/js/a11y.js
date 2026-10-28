import '../css/feedback.css';

// Accessible replacements for the browser's blocking confirm() / alert(), plus the
// focus helpers the layout's dropdowns and dialogs use.

const FOCUSABLE = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

const focusables = (root) => [...root.querySelectorAll(FOCUSABLE)].filter((el) => el.getClientRects().length > 0);

// Keep Tab / Shift+Tab inside an open dialog or dropdown panel.
window.crmTrapTab = (event, root) => {
    if (event.key !== 'Tab' || !root) return;
    const items = focusables(root);
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (!root.contains(document.activeElement)) {
        event.preventDefault();
        first.focus();
    } else if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

window.crmFocusFirst = (root) => {
    if (!root) return;
    const [first] = focusables(root);
    (first || root).focus?.();
};

/* ── Confirm dialog ─────────────────────────────────────────────────────── */

const DANGER_WORDS = /delete|remove|cancel|reject|absent|permanent|discard|void|revoke|deactivate|suspend|reset/i;
let dialog = null;

const buildDialog = () => {
    const backdrop = document.createElement('div');
    backdrop.className = 'crm-dialog-backdrop';
    backdrop.hidden = true;
    backdrop.innerHTML = `
        <div class="crm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="crm-dialog-title" aria-describedby="crm-dialog-message">
            <h2 id="crm-dialog-title" class="crm-dialog__title"></h2>
            <p id="crm-dialog-message" class="crm-dialog__message"></p>
            <div class="crm-dialog__actions">
                <button type="button" class="crm-dialog__btn btn-icon" data-action="cancel"></button>
                <button type="button" class="crm-dialog__btn crm-dialog__btn--primary btn-icon" data-action="confirm"></button>
            </div>
        </div>`;
    document.body.appendChild(backdrop);
    return backdrop;
};

// Resolves true when the user confirms, false on Cancel, Escape or a backdrop click.
window.crmConfirm = (message, options = {}) => new Promise((resolve) => {
    dialog ??= buildDialog();
    const text = String(message ?? '');
    const danger = options.danger ?? DANGER_WORDS.test(text);
    const panel = dialog.querySelector('.crm-dialog');
    const okButton = dialog.querySelector('[data-action="confirm"]');
    const cancelButton = dialog.querySelector('[data-action="cancel"]');

    dialog.querySelector('.crm-dialog__title').textContent = options.title || (danger ? 'Please confirm' : 'Confirm action');
    dialog.querySelector('.crm-dialog__message').textContent = text;
    okButton.textContent = options.confirmText || (danger ? 'Yes, continue' : 'Confirm');
    cancelButton.textContent = options.cancelText || 'Cancel';
    okButton.classList.toggle('crm-dialog__btn--danger', danger);

    const returnFocus = document.activeElement;
    dialog.hidden = false;
    document.documentElement.classList.add('crm-dialog-open');
    // Destructive actions start on Cancel so Enter never deletes by accident.
    (danger ? cancelButton : okButton).focus();

    const close = (result) => {
        dialog.hidden = true;
        document.documentElement.classList.remove('crm-dialog-open');
        dialog.removeEventListener('keydown', onKeydown);
        dialog.removeEventListener('click', onClick);
        returnFocus?.focus?.();
        resolve(result);
    };
    const onKeydown = (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            close(false);
        } else {
            window.crmTrapTab(event, panel);
        }
    };
    const onClick = (event) => {
        const action = event.target.closest('[data-action]');
        if (action) close(action.dataset.action === 'confirm');
        else if (event.target === dialog) close(false);
    };
    dialog.addEventListener('keydown', onKeydown);
    dialog.addEventListener('click', onClick);
});

// <form data-confirm="…">: ask before the form submits.
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) return;
    if (form.dataset.crmConfirmed === '1') {
        delete form.dataset.crmConfirmed;
        return;
    }
    event.preventDefault();
    event.stopImmediatePropagation();
    const submitter = event.submitter;
    if (await window.crmConfirm(form.dataset.confirm)) {
        form.dataset.crmConfirmed = '1';
        submitter ? form.requestSubmit(submitter) : form.requestSubmit();
    }
}, true);

// <button data-confirm="…"> / <a data-confirm="…">: ask, then repeat the original click.
document.addEventListener('click', async (event) => {
    const el = event.target.closest('[data-confirm]');
    if (!el || el.tagName === 'FORM') return;
    if (el.dataset.crmConfirmed === '1') {
        delete el.dataset.crmConfirmed;
        return;
    }
    event.preventDefault();
    event.stopImmediatePropagation();
    if (await window.crmConfirm(el.dataset.confirm)) {
        el.dataset.crmConfirmed = '1';
        el.click();
    }
}, true);

/* ── Alert toasts ───────────────────────────────────────────────────────── */

const PENDING_KEY = 'crm_pending_toasts';
const raised = [];
const CLOSE_ICON = '<svg class="w-3.5 h-3.5 pointer-events-none" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

const toneFor = (text) => {
    if (/error|fail|unable|not supported|unavailable|blocked|denied|invalid|could not|🚫|❌/i.test(text)) return 'error';
    if (/copied|sent|saved|success|done|✅/i.test(text)) return 'success';
    return 'warning';
};

// Non-blocking replacement for alert(): errors stay until dismissed, others fade.
window.crmAlert = (message, tone) => {
    const text = String(message ?? '');
    tone ||= toneFor(text);

    let stack = document.querySelector('.crm-toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'crm-toast-stack';
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
    }

    const toast = document.createElement('div');
    toast.className = `crm-toast crm-toast--${tone}`;
    toast.setAttribute('role', tone === 'error' ? 'alert' : 'status');
    const body = document.createElement('p');
    body.className = 'crm-toast__text';
    body.textContent = text;
    const dismiss = document.createElement('button');
    dismiss.type = 'button';
    dismiss.className = 'crm-toast__close btn-icon';
    dismiss.setAttribute('aria-label', 'Dismiss message');
    dismiss.innerHTML = CLOSE_ICON;
    dismiss.addEventListener('click', () => toast.remove());
    toast.append(body, dismiss);
    stack.append(toast);

    raised.push({ text, tone, at: Date.now(), el: toast });
    if (tone !== 'error') setTimeout(() => toast.remove(), tone === 'success' ? 5000 : 8000);
};

// A toast raised right before a redirect or reload is shown again on the next page.
window.addEventListener('pagehide', () => {
    const recent = raised
        .filter((t) => t.el.isConnected && Date.now() - t.at < 4000)
        .map(({ text, tone }) => ({ text, tone }));
    try {
        if (recent.length) sessionStorage.setItem(PENDING_KEY, JSON.stringify(recent));
    } catch (e) {}
});

const showPendingToasts = () => {
    let saved = [];
    try {
        saved = JSON.parse(sessionStorage.getItem(PENDING_KEY) || '[]');
        sessionStorage.removeItem(PENDING_KEY);
    } catch (e) {}
    saved.forEach((t) => window.crmAlert(t.text, t.tone));
};
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', showPendingToasts);
else showPendingToasts();
