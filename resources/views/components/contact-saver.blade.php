{{-- Universal Contact Saver Engine (Google Contacts Web & Mobile Google Account Sync) --}}
<div id="desktop-contact-sync-modal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity duration-200" aria-modal="true" role="dialog">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6 text-left relative transform transition-all">
        {{-- Close Button --}}
        <button type="button" onclick="closeDesktopContactSyncModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition" aria-label="Close dialog">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Save Customer Contact</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Save in Google Contacts to auto-sync with WhatsApp</p>
            </div>
        </div>

        {{-- Customer Info Preview Card --}}
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-3.5 mb-4">
            <div class="flex items-center justify-between mb-1.5">
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Customer Name</div>
                <div id="dcs-modal-name" class="text-xs font-bold text-slate-900 dark:text-white">Customer</div>
            </div>
            <div class="flex items-center justify-between mb-1.5">
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Phone Number</div>
                <div id="dcs-modal-phone" class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">+91 00000 00000</div>
            </div>
            <div id="dcs-modal-email-row" class="flex items-center justify-between mb-1.5 hidden">
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Email Address</div>
                <div id="dcs-modal-email" class="text-xs font-mono font-semibold text-slate-700 dark:text-slate-300"></div>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-emerald-600 dark:text-emerald-400 mt-2 pt-2 border-t border-slate-200 dark:border-slate-700/50">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Google Account Cloud Sync: Auto-prefilled for WhatsApp</span>
            </div>
        </div>

        {{-- Explanatory Note for WhatsApp Search --}}
        <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/40 rounded-xl p-3 mb-4 text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-2.5">
            <div class="shrink-0 mt-0.5">
                <x-icon.whatsapp class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="leading-relaxed">
                <strong>WhatsApp Auto-Sync:</strong> Once you click <strong>Save</strong> in Google Contacts, searching <strong id="dcs-modal-name-highlight" class="text-emerald-800 dark:text-emerald-300">Customer</strong> in WhatsApp will immediately find their chat to share your quotation PDF!
            </div>
        </div>

        {{-- Actions: Single Google Contacts Web Method Only --}}
        <div class="space-y-2">
            <button type="button" onclick="openGoogleContactsFromModal()" class="w-full btn-primary text-xs py-2.5 px-3 flex items-center justify-center gap-2 font-semibold shadow-xs" style="background-color: #059669 !important; color: #ffffff !important;">
                <svg class="w-4 h-4 shrink-0 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                </svg>
                <span style="color: #ffffff !important;">Save in Google Contacts (Web)</span>
            </button>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-right">
            <button type="button" onclick="closeDesktopContactSyncModal()" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-medium transition">
                Done
            </button>
        </div>
    </div>
</div>

{{-- Universal Toast Notification Container --}}
<div id="contact-saver-toast" class="fixed top-5 right-5 z-[9999] hidden max-w-sm w-full bg-slate-900 text-white rounded-xl shadow-2xl p-4 text-xs flex items-start gap-3 border border-slate-800 transform transition-all duration-300">
    <div id="contact-saver-toast-icon" class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">
        ✓
    </div>
    <div class="flex-1">
        <div id="contact-saver-toast-title" class="font-bold text-white text-xs mb-0.5">Contacts App</div>
        <div id="contact-saver-toast-msg" class="text-slate-300 leading-relaxed text-[11px]">Contact processed successfully.</div>
    </div>
    <button type="button" onclick="hideContactSaverToast()" class="text-slate-400 hover:text-white p-0.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<script>
    let activeContactSyncData = null;
    let contactSaverToastTimer = null;

    function buildGoogleContactsUrl(data) {
        if (!data) return 'https://contacts.google.com/new';
        const cleanName = (data.name || 'Customer').trim();
        const parts = cleanName.split(/\s+/);
        const firstName = parts[0] || '';
        const lastName = parts.slice(1).join(' ') || '';

        let phone = (data.phone || data.rawPhone || '').trim();
        if (phone && !phone.startsWith('+')) {
            const digits = phone.replace(/[^0-9]/g, '');
            if (digits.length === 10) {
                phone = '+91' + digits;
            } else if (digits.length > 10) {
                phone = '+' + digits;
            }
        }
        const email = (data.email || '').trim();

        const params = new URLSearchParams();
        if (firstName) params.set('givenname', firstName);
        if (lastName) params.set('familyname', lastName);
        if (phone) params.set('phone', phone);
        if (email) params.set('email', email);

        return `https://contacts.google.com/new?${params.toString()}`;
    }

    function showContactSaverToast(title, message, isSuccess = true, duration = 5000) {
        const toast = document.getElementById('contact-saver-toast');
        const titleEl = document.getElementById('contact-saver-toast-title');
        const msgEl = document.getElementById('contact-saver-toast-msg');
        const iconEl = document.getElementById('contact-saver-toast-icon');

        if (!toast || !titleEl || !msgEl) return;

        titleEl.innerText = title;
        msgEl.innerHTML = message;

        if (iconEl) {
            iconEl.className = isSuccess 
                ? 'w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs' 
                : 'w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs';
            iconEl.innerText = isSuccess ? '✓' : '!';
        }

        toast.classList.remove('hidden');
        if (contactSaverToastTimer) clearTimeout(contactSaverToastTimer);
        contactSaverToastTimer = setTimeout(() => {
            hideContactSaverToast();
        }, duration);
    }

    function hideContactSaverToast() {
        const toast = document.getElementById('contact-saver-toast');
        if (toast) toast.classList.add('hidden');
        if (contactSaverToastTimer) clearTimeout(contactSaverToastTimer);
    }

    /**
     * Universal Contact Saver Function
     * Automatically opens Google Contacts Web with pre-filled Name, Phone, and Email.
     */
    window.triggerSaveContactDirect = function(contact) {
        if (!contact) return;

        const rawPhone = contact.phone || '';
        const cleanPhone = String(rawPhone).replace(/[^0-9+]/g, '');
        const cleanName = (contact.name || 'Customer').trim();
        const vcardUrl = contact.vcardUrl || '';

        activeContactSyncData = {
            name: cleanName,
            phone: cleanPhone,
            rawPhone: rawPhone,
            email: contact.email || '',
            address: contact.address || '',
            company: contact.company || 'Precision IT Systems',
            vcardUrl: vcardUrl
        };

        const ua = navigator.userAgent || '';
        const isAndroid = /Android/i.test(ua);
        const isIOS = /iPhone|iPad|iPod/i.test(ua) && !window.MSStream;

        // 1. Android Native Contacts App Integration (saves straight to Google Account on phone)
        if (isAndroid) {
            const extras = [
                'S.name=' + encodeURIComponent(cleanName),
                'S.phone=' + encodeURIComponent(cleanPhone)
            ];
            if (contact.email) extras.push('S.email=' + encodeURIComponent(contact.email));
            if (contact.address) extras.push('S.postal=' + encodeURIComponent(contact.address));
            if (contact.company) extras.push('S.company=' + encodeURIComponent(contact.company));

            const intentUrl = 'intent:#Intent;action=android.intent.action.INSERT;type=vnd.android.cursor.dir/contact;' + extras.join(';') + ';end';

            showContactSaverToast(
                '📱 Opening Contacts App',
                `Tap <strong>Save</strong> to add to Google Account. Once saved, WhatsApp will recognize <strong>${cleanName}</strong>!`,
                true,
                6000
            );

            window.location.href = intentUrl;
            return;
        }

        // 2. iOS Native Contact Card Integration
        if (isIOS) {
            showContactSaverToast(
                '📱 Opening Apple Contact Card',
                `Tap <strong>Create New Contact</strong> to save to your iPhone. WhatsApp will then recognize <strong>${cleanName}</strong>!`,
                true,
                6000
            );

            if (vcardUrl) {
                window.location.href = vcardUrl;
            }
            return;
        }

        // 3. Desktop (Windows / Mac) Integration -> Directly open Google Contacts Web with pre-filled fields
        const googleUrl = buildGoogleContactsUrl(activeContactSyncData);

        // Copy contact details to clipboard as instant backup
        try {
            const clipText = `${cleanName}\n${cleanPhone}${activeContactSyncData.email ? '\n' + activeContactSyncData.email : ''}`;
            navigator.clipboard.writeText(clipText);
        } catch (e) {}

        // Automatically launch Google Contacts Web in a new tab
        window.open(googleUrl, '_blank');

        showContactSaverToast(
            '🌐 Opening Google Contacts Web',
            `Pre-filled <strong>${cleanName}</strong> and <strong>${cleanPhone}</strong>. Click <strong>Save</strong> in Google Contacts to sync with WhatsApp!`,
            true,
            6000
        );

        // Open modal as on-screen guide
        openDesktopContactSyncModal(activeContactSyncData);
    };

    function openDesktopContactSyncModal(data) {
        const modal = document.getElementById('desktop-contact-sync-modal');
        if (!modal) return;

        const nameEl = document.getElementById('dcs-modal-name');
        const phoneEl = document.getElementById('dcs-modal-phone');
        const emailRowEl = document.getElementById('dcs-modal-email-row');
        const emailEl = document.getElementById('dcs-modal-email');
        const highlightEl = document.getElementById('dcs-modal-name-highlight');

        if (nameEl) nameEl.innerText = data.name || 'Customer';
        if (phoneEl) phoneEl.innerText = data.phone || data.rawPhone || 'No Phone';
        if (highlightEl) highlightEl.innerText = data.name || 'Customer';

        if (emailRowEl && emailEl) {
            if (data.email) {
                emailEl.innerText = data.email;
                emailRowEl.classList.remove('hidden');
            } else {
                emailRowEl.classList.add('hidden');
            }
        }

        modal.classList.remove('hidden');
    }

    function closeDesktopContactSyncModal() {
        const modal = document.getElementById('desktop-contact-sync-modal');
        if (modal) modal.classList.add('hidden');
    }

    async function openGoogleContactsFromModal() {
        if (!activeContactSyncData) return;

        const googleUrl = buildGoogleContactsUrl(activeContactSyncData);

        // Copy Name, Phone & Email to clipboard for instant pasting if desired
        const clipText = `${activeContactSyncData.name}\n${activeContactSyncData.phone}${activeContactSyncData.email ? '\n' + activeContactSyncData.email : ''}`;
        try {
            await navigator.clipboard.writeText(clipText);
            showContactSaverToast('✓ Copied to Clipboard', `Copied "${activeContactSyncData.name}" & "${activeContactSyncData.phone}". Paste directly into Google Contacts.`);
        } catch (e) {}

        // Open Google Contacts Create Contact in new tab
        window.open(googleUrl, '_blank');
    }
</script>