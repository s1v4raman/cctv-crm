/**
 * Precision CCTV CRM - Universal Searchable Combobox Select Engine
 * Provides instant clickable search bar filtering across all modules:
 * Customer Name, Installation Jobs, Product Catalog, Site Location,
 * Projects, Suppliers, Purchase Orders, and Service Tickets.
 */

class CrmSearchableSelect {
    constructor(selectEl, customOptions = {}) {
        if (!selectEl || selectEl.dataset.crmSearchableInitialized) return;
        this.select = selectEl;
        this.options = customOptions;
        this.isOpen = false;
        this.highlightedIndex = -1;
        this.items = [];
        this.filteredItems = [];

        this.init();
    }

    init() {
        this.select.dataset.crmSearchableInitialized = 'true';

        // Detect field type for contextual placeholder
        const fieldName = (this.select.name || this.select.id || '').toLowerCase();
        let placeholder = 'Type to search options...';
        if (fieldName.includes('product')) {
            placeholder = 'Search product model, name, or stock...';
        } else if (fieldName.includes('lead') || fieldName.includes('customer')) {
            placeholder = 'Search customer name, phone, address...';
        } else if (fieldName.includes('job')) {
            placeholder = 'Search job no, client, or site...';
        } else if (fieldName.includes('supplier')) {
            placeholder = 'Search supplier name, company...';
        } else if (fieldName.includes('site')) {
            placeholder = 'Search site location, client, address...';
        } else if (fieldName.includes('project')) {
            placeholder = 'Search project code, title, site...';
        } else if (fieldName.includes('purchase') || fieldName.includes('po')) {
            placeholder = 'Search purchase order, supplier...';
        } else if (fieldName.includes('worker')) {
            placeholder = 'Search worker by name or phone...';
        }
        this.searchPlaceholder = this.options.placeholder || placeholder;

        // Parse options from native select
        this.parseOptions();

        // Build UI elements
        this.buildUI();

        // Hide original select but keep in DOM for native form submission & validation
        this.select.style.cssText = 'position: absolute !important; opacity: 0 !important; pointer-events: none !important; width: 0 !important; height: 0 !important; margin: 0 !important; padding: 0 !important; border: 0 !important;';
        this.select.tabIndex = -1;

        // Bind events
        this.bindEvents();

        // Initial sync of display value
        this.syncFromSelect();

        // Watch for changes on the original select (programmatic updates or quick add modals)
        this.observer = new MutationObserver(() => {
            this.parseOptions();
            this.renderList();
            this.syncFromSelect();
        });
        this.observer.observe(this.select, { childList: true, subtree: true, attributes: true, attributeFilter: ['selected'] });
    }

    parseOptions() {
        this.items = [];
        const children = Array.from(this.select.children);

        let globalIndex = 0;
        children.forEach(child => {
            if (child.tagName === 'OPTGROUP') {
                const groupLabel = child.label || '';
                Array.from(child.children).forEach(opt => {
                    this.items.push(this.formatOptionData(opt, groupLabel, globalIndex++));
                });
            } else if (child.tagName === 'OPTION') {
                this.items.push(this.formatOptionData(child, null, globalIndex++));
            }
        });

        this.filteredItems = [...this.items];
    }

    formatOptionData(optionEl, groupLabel, index) {
        const rawText = (optionEl.textContent || '').trim();
        const value = optionEl.value;
        const isPlaceholder = value === '' || rawText.startsWith('--') || rawText.toLowerCase().includes('select');

        // Extract metadata: [Stock: X]
        let stockBadge = null;
        let stockVal = null;
        const stockMatch = rawText.match(/\[Stock:\s*(\d+)\]/i);
        if (stockMatch) {
            stockVal = parseInt(stockMatch[1], 10);
            stockBadge = {
                count: stockVal,
                label: `Stock: ${stockVal}`,
                isLow: stockVal === 0,
            };
        }

        // Clean label without the bracketed stock
        let cleanText = rawText.replace(/\[Stock:\s*\d+\]/gi, '').trim();

        // Extract secondary subtitle: (Phone) or — Site Address
        let subtitle = null;
        let mainTitle = cleanText;

        // Check for phone / company in parentheses e.g. "Customer Name (9876543210)"
        const parenMatch = cleanText.match(/^(.*?)\s*\(([^)]+)\)\s*(.*)$/);
        if (parenMatch) {
            mainTitle = parenMatch[1].trim();
            const insideParen = parenMatch[2].trim();
            const rest = parenMatch[3].trim();
            subtitle = rest ? `${insideParen} • ${rest}` : insideParen;
        } else if (cleanText.includes('—')) {
            const parts = cleanText.split('—');
            mainTitle = parts[0].trim();
            subtitle = parts.slice(1).join('—').trim();
        } else if (cleanText.includes(' - ')) {
            const parts = cleanText.split(' - ');
            mainTitle = parts[0].trim();
            subtitle = parts.slice(1).join(' - ').trim();
        }

        return {
            index,
            value,
            rawText,
            mainTitle,
            subtitle,
            stockBadge,
            group: groupLabel,
            disabled: optionEl.disabled,
            selected: optionEl.selected,
            isPlaceholder,
            searchText: `${rawText} ${groupLabel || ''}`.toLowerCase()
        };
    }

    buildUI() {
        // Main wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'crm-searchable-combobox relative w-full';

        // Trigger button
        this.trigger = document.createElement('button');
        this.trigger.type = 'button';
        this.trigger.className = 'crm-combobox-trigger w-full h-[44px] min-h-[44px] px-3.5 flex items-center justify-between text-left text-sm rounded-lg bg-[var(--card-bg,#fff)] dark:bg-slate-900 border border-[var(--input-border,#cbd5e1)] dark:border-slate-700 text-[var(--text-main,#0f172a)] dark:text-slate-100 hover:border-slate-400 dark:hover:border-slate-500 focus:outline-none focus:border-[var(--crm-form-border-color, var(--crm-accent, #2563eb))] focus:shadow-none active:bg-white dark:active:bg-slate-900 transition-all cursor-pointer shadow-xs';
        this.trigger.setAttribute('aria-haspopup', 'listbox');
        this.trigger.setAttribute('aria-expanded', 'false');

        // Trigger left: selected label & optional stock pill
        this.triggerLeft = document.createElement('div');
        this.triggerLeft.className = 'flex items-center gap-2 truncate pr-2 flex-1 min-w-0';

        this.labelSpan = document.createElement('span');
        this.labelSpan.className = 'crm-combobox-label truncate font-medium text-slate-800 dark:text-slate-200';
        this.triggerLeft.appendChild(this.labelSpan);

        this.badgeSpan = document.createElement('span');
        this.badgeSpan.className = 'crm-combobox-badge hidden shrink-0';
        this.triggerLeft.appendChild(this.badgeSpan);

        // Trigger right: clear button & chevron
        this.triggerRight = document.createElement('div');
        this.triggerRight.className = 'flex items-center gap-1.5 shrink-0 ml-1 text-slate-400';

        this.clearBtn = document.createElement('button');
        this.clearBtn.type = 'button';
        this.clearBtn.className = 'crm-combobox-clear hidden p-1 rounded-md hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer';
        this.clearBtn.title = 'Clear selection';
        this.clearBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

        this.chevronIcon = document.createElement('span');
        this.chevronIcon.className = 'crm-combobox-chevron transition-transform duration-200 flex items-center';
        this.chevronIcon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>';

        this.triggerRight.appendChild(this.clearBtn);
        this.triggerRight.appendChild(this.chevronIcon);

        this.trigger.appendChild(this.triggerLeft);
        this.trigger.appendChild(this.triggerRight);

        // Dropdown panel
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'crm-combobox-dropdown hidden absolute left-0 right-0 z-[9999] bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl overflow-hidden mt-1 transition-all duration-150';

        // Search Bar Section
        this.searchHeader = document.createElement('div');
        this.searchHeader.className = 'crm-combobox-search-header p-2.5 bg-slate-50 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700/80';

        const searchWrapper = document.createElement('div');
        searchWrapper.className = 'relative flex items-center';

        const searchIcon = document.createElement('div');
        searchIcon.className = 'absolute left-3 text-slate-400 pointer-events-none flex items-center';
        searchIcon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>';

        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'crm-combobox-search-input w-full h-[38px] pl-9 pr-8 text-xs sm:text-sm rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-[var(--crm-form-border-color, var(--crm-accent, #2563eb))] focus:shadow-none font-medium';
        this.searchInput.placeholder = this.searchPlaceholder;
        this.searchInput.autocomplete = 'off';
        this.searchInput.spellcheck = false;

        this.searchClearBtn = document.createElement('button');
        this.searchClearBtn.type = 'button';
        this.searchClearBtn.className = 'crm-combobox-search-clear hidden absolute right-2.5 p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-200';
        this.searchClearBtn.title = 'Clear search';
        this.searchClearBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

        searchWrapper.appendChild(searchIcon);
        searchWrapper.appendChild(this.searchInput);
        searchWrapper.appendChild(this.searchClearBtn);

        // Sub-hint counter bar
        this.hintBar = document.createElement('div');
        this.hintBar.className = 'flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 px-1';
        this.countSpan = document.createElement('span');
        this.countSpan.className = 'crm-combobox-count font-medium';
        this.countSpan.textContent = `${this.items.length} options available`;
        
        const navHint = document.createElement('span');
        navHint.className = 'hidden sm:inline text-slate-400';
        navHint.textContent = '↑↓ navigate • ↵ select • esc';

        this.hintBar.appendChild(this.countSpan);
        this.hintBar.appendChild(navHint);

        this.searchHeader.appendChild(searchWrapper);
        this.searchHeader.appendChild(this.hintBar);

        // Option List Container
        this.listContainer = document.createElement('div');
        this.listContainer.className = 'crm-combobox-list max-h-[280px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/40 py-1 text-xs sm:text-sm custom-scrollbar';
        this.listContainer.setAttribute('role', 'listbox');

        // Empty Search Results State
        this.emptyState = document.createElement('div');
        this.emptyState.className = 'crm-combobox-empty hidden p-6 text-center text-xs';
        this.emptyState.innerHTML = `
            <div class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
            </div>
            <p class="font-bold text-slate-700 dark:text-slate-300">No matching results found</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Try searching with a different name, keyword, or model number.</p>
        `;

        this.dropdown.appendChild(this.searchHeader);
        this.dropdown.appendChild(this.listContainer);
        this.dropdown.appendChild(this.emptyState);

        this.wrapper.appendChild(this.trigger);
        this.wrapper.appendChild(this.dropdown);

        // Insert wrapper right after the native select
        this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);

        // Render initial options
        this.renderList();
    }

    renderList() {
        this.listContainer.innerHTML = '';

        if (this.filteredItems.length === 0) {
            this.emptyState.classList.remove('hidden');
            this.countSpan.textContent = '0 results found';
            return;
        }

        this.emptyState.classList.add('hidden');
        this.countSpan.textContent = `${this.filteredItems.length} result${this.filteredItems.length === 1 ? '' : 's'}`;

        let currentGroup = null;

        this.filteredItems.forEach((item, fIndex) => {
            // Group Header
            if (item.group && item.group !== currentGroup) {
                currentGroup = item.group;
                const groupHeader = document.createElement('div');
                groupHeader.className = 'px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-100/80 dark:bg-slate-800/70 sticky top-0 z-10 border-y border-slate-200/50 dark:border-slate-700/50';
                groupHeader.textContent = currentGroup;
                this.listContainer.appendChild(groupHeader);
            }

            const itemEl = document.createElement('div');
            itemEl.className = 'crm-combobox-item px-3.5 py-2.5 cursor-pointer flex items-center justify-between gap-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors select-none';
            itemEl.dataset.filteredIndex = fIndex;
            itemEl.dataset.value = item.value;
            itemEl.setAttribute('role', 'option');

            const isSelected = String(this.select.value) === String(item.value);
            if (isSelected) {
                itemEl.classList.add('is-selected', 'bg-slate-50', 'dark:bg-slate-800/80', 'font-semibold');
            }

            // Left text block (Title & Subtitle)
            const textWrap = document.createElement('div');
            textWrap.className = 'flex-1 min-w-0';

            const titleDiv = document.createElement('div');
            titleDiv.className = 'crm-item-primary font-medium text-slate-900 dark:text-slate-100 truncate';
            titleDiv.textContent = item.mainTitle || item.rawText;
            textWrap.appendChild(titleDiv);

            if (item.subtitle) {
                const subDiv = document.createElement('div');
                subDiv.className = 'crm-item-sub text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5';
                subDiv.textContent = item.subtitle;
                textWrap.appendChild(subDiv);
            }

            itemEl.appendChild(textWrap);

            // Right block: Stock badge + selected checkmark
            const rightWrap = document.createElement('div');
            rightWrap.className = 'flex items-center gap-2 shrink-0';

            if (item.stockBadge) {
                const badge = document.createElement('span');
                if (item.stockBadge.count > 0) {
                    badge.className = 'px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
                } else {
                    badge.className = 'px-2 py-0.5 text-[11px] font-bold rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60';
                }
                badge.textContent = item.stockBadge.label;
                rightWrap.appendChild(badge);
            }

            if (isSelected) {
                const check = document.createElement('span');
                check.className = 'shrink-0';
                check.style.color = 'var(--crm-form-border-color, var(--crm-accent, #2563eb))';
                check.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
                rightWrap.appendChild(check);
            }

            itemEl.appendChild(rightWrap);

            // Click option to select
            itemEl.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectItem(item);
            });

            this.listContainer.appendChild(itemEl);
        });
    }

    bindEvents() {
        // Toggle dropdown on trigger click
        this.trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        });

        // Clear button
        this.clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.selectValue('');
        });

        // Search input input event
        this.searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim().toLowerCase();
            if (query) {
                this.searchClearBtn.classList.remove('hidden');
            } else {
                this.searchClearBtn.classList.add('hidden');
            }
            this.filter(query);
        });

        // Search clear button
        this.searchClearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.searchInput.value = '';
            this.searchClearBtn.classList.add('hidden');
            this.filter('');
            this.searchInput.focus();
        });

        // Keyboard navigation in search input
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.moveHighlight(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.moveHighlight(-1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredItems.length) {
                    this.selectItem(this.filteredItems[this.highlightedIndex]);
                } else if (this.filteredItems.length === 1) {
                    this.selectItem(this.filteredItems[0]);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                this.close();
                this.trigger.focus();
            }
        });

        // Listen for changes on the original select (external or programmatic updates)
        this.select.addEventListener('change', () => {
            this.syncFromSelect();
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.wrapper.contains(e.target)) {
                this.close();
            }
        });

        // Close on window resize / scroll if needed
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    open() {
        // Close any other open comboboxes
        document.querySelectorAll('.crm-searchable-combobox.is-open').forEach(cb => {
            if (cb !== this.wrapper && cb.__crmCombobox) {
                cb.__crmCombobox.close();
            }
        });

        this.wrapper.__crmCombobox = this;
        this.wrapper.classList.add('is-open');
        this.dropdown.classList.remove('hidden');
        this.trigger.setAttribute('aria-expanded', 'true');
        this.chevronIcon.classList.add('rotate-180');

        // Always open DOWNWARDS directly below the trigger (matches Image 5 design)
        this.dropdown.style.top = 'calc(100% + 4px)';
        this.dropdown.style.bottom = 'auto';

        this.isOpen = true;
        this.highlightedIndex = -1;

        // Reset filter
        this.searchInput.value = '';
        this.searchClearBtn.classList.add('hidden');
        this.filter('');

        // Focus search input and scroll dropdown into view if needed
        requestAnimationFrame(() => {
            this.searchInput.focus();
            const dropRect = this.dropdown.getBoundingClientRect();
            if (dropRect.bottom > window.innerHeight) {
                window.scrollBy({ top: dropRect.bottom - window.innerHeight + 24, behavior: 'smooth' });
            }
            const selectedEl = this.listContainer.querySelector('.bg-blue-50\\/70, .dark\\:bg-blue-950\\/40');
            if (selectedEl) {
                selectedEl.scrollIntoView({ block: 'nearest' });
            }
        });

        // Focus search input
        requestAnimationFrame(() => {
            this.searchInput.focus();
            // Scroll to selected item if any
            const selectedEl = this.listContainer.querySelector('.bg-blue-50\\/70, .dark\\:bg-blue-950\\/40');
            if (selectedEl) {
                selectedEl.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    close() {
        this.wrapper.classList.remove('is-open');
        this.dropdown.classList.add('hidden');
        this.trigger.setAttribute('aria-expanded', 'false');
        this.chevronIcon.classList.remove('rotate-180');
        this.isOpen = false;
        this.highlightedIndex = -1;
    }

    filter(query) {
        if (!query) {
            this.filteredItems = [...this.items];
        } else {
            const tokens = query.split(/\s+/).filter(Boolean);
            this.filteredItems = this.items.filter(item => {
                if (item.isPlaceholder) return false;
                return tokens.every(token => item.searchText.includes(token));
            });
        }
        this.renderList();
    }

    moveHighlight(direction) {
        const itemsCount = this.filteredItems.length;
        if (itemsCount === 0) return;

        this.highlightedIndex += direction;
        if (this.highlightedIndex < 0) this.highlightedIndex = itemsCount - 1;
        if (this.highlightedIndex >= itemsCount) this.highlightedIndex = 0;

        // Update visual highlight
        const renderedItems = this.listContainer.querySelectorAll('.crm-combobox-item');
        renderedItems.forEach((el, idx) => {
            if (idx === this.highlightedIndex) {
                el.classList.add('bg-blue-100/80', 'dark:bg-blue-900/60', 'ring-1', 'ring-blue-500');
                el.scrollIntoView({ block: 'nearest' });
            } else {
                el.classList.remove('bg-blue-100/80', 'dark:bg-blue-900/60', 'ring-1', 'ring-blue-500');
            }
        });
    }

    selectItem(item) {
        this.selectValue(item.value);
        this.close();
        this.trigger.focus();
    }

    selectValue(value) {
        this.select.value = value;
        // Dispatch bubbling events so Alpine & vanilla JS listeners catch it
        this.select.dispatchEvent(new Event('input', { bubbles: true }));
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
        this.syncFromSelect();
    }

    syncFromSelect() {
        const val = this.select.value;
        const currentItem = this.items.find(i => String(i.value) === String(val));

        if (currentItem && !currentItem.isPlaceholder && currentItem.value !== '') {
            this.labelSpan.textContent = currentItem.mainTitle || currentItem.rawText;
            this.labelSpan.classList.remove('text-slate-400', 'dark:text-slate-500');
            this.labelSpan.classList.add('text-slate-900', 'dark:text-slate-100');

            if (currentItem.stockBadge) {
                this.badgeSpan.className = currentItem.stockBadge.count > 0 
                    ? 'crm-combobox-badge px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60'
                    : 'crm-combobox-badge px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60';
                this.badgeSpan.textContent = currentItem.stockBadge.label;
                this.badgeSpan.classList.remove('hidden');
            } else {
                this.badgeSpan.classList.add('hidden');
            }

            this.clearBtn.classList.remove('hidden');
        } else {
            // Find placeholder text from first option or select attribute
            const firstOpt = this.items[0];
            const placeholder = (firstOpt && firstOpt.isPlaceholder) ? firstOpt.rawText : '-- Select Option --';
            this.labelSpan.textContent = placeholder;
            this.labelSpan.classList.add('text-slate-400', 'dark:text-slate-500');
            this.labelSpan.classList.remove('text-slate-900', 'dark:text-slate-100');
            this.badgeSpan.classList.add('hidden');
            this.clearBtn.classList.add('hidden');
        }

        // Re-render items to update checkmarks
        this.renderList();
    }
}

// Injection of combobox outline styles
function ensureComboboxStyles() {
    if (typeof document === 'undefined' || document.getElementById('crm-combobox-styles')) return;
    const style = document.createElement('style');
    style.id = 'crm-combobox-styles';
    style.textContent = `
        /* Strictly ONLY outline color on searchable dropdown trigger - NEVER background fill */
        .crm-combobox-trigger,
        .crm-combobox-trigger:hover,
        .crm-combobox-trigger:focus,
        .crm-combobox-trigger:focus-visible,
        .crm-combobox-trigger:active,
        .crm-combobox-trigger[aria-expanded="true"],
        .crm-searchable-combobox.is-open .crm-combobox-trigger {
            background: #ffffff !important;
            background-color: #ffffff !important;
            background-image: none !important;
            transform: none !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
        }
        html.dark .crm-combobox-trigger,
        html.dark .crm-combobox-trigger:hover,
        html.dark .crm-combobox-trigger:focus,
        html.dark .crm-combobox-trigger:focus-visible,
        html.dark .crm-combobox-trigger:active,
        html.dark .crm-combobox-trigger[aria-expanded="true"],
        html.dark .crm-searchable-combobox.is-open .crm-combobox-trigger {
            background: #0f172a !important;
            background-color: #0f172a !important;
            background-image: none !important;
            transform: none !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
        }
        .crm-combobox-trigger:focus,
        .crm-combobox-trigger:focus-visible,
        .crm-combobox-trigger:active,
        .crm-combobox-trigger[aria-expanded="true"],
        .crm-searchable-combobox.is-open .crm-combobox-trigger {
            border-color: var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
            outline: 2px solid var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
            outline-offset: -1px !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            --tw-ring-color: transparent !important;
            --tw-ring-shadow: 0 0 #0000 !important;
            --tw-ring-offset-shadow: 0 0 #0000 !important;
            --tw-shadow: 0 0 #0000 !important;
        }
        /* Search bar inside dropdown */
        .crm-combobox-search-input,
        .crm-combobox-search-input:focus,
        .crm-combobox-search-input:focus-visible {
            border-color: var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
            outline: 2px solid var(--crm-form-border-color, var(--crm-accent, #2563eb)) !important;
            outline-offset: -1px !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            --tw-ring-color: transparent !important;
            --tw-ring-shadow: 0 0 #0000 !important;
            --tw-ring-offset-shadow: 0 0 #0000 !important;
            --tw-shadow: 0 0 #0000 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
        }
        html.dark .crm-combobox-search-input,
        html.dark .crm-combobox-search-input:focus,
        html.dark .crm-combobox-search-input:focus-visible {
            background: #0f172a !important;
            background-color: #0f172a !important;
        }
        /* Dropdown options list styling */
        .crm-combobox-item {
            background-color: transparent !important;
        }
        .crm-combobox-item:hover {
            background-color: #f1f5f9 !important;
        }
        html.dark .crm-combobox-item:hover {
            background-color: #1e293b !important;
        }
        .crm-combobox-item.is-selected {
            background-color: #f8fafc !important;
        }
        html.dark .crm-combobox-item.is-selected {
            background-color: #1e293b !important;
        }
    `;
    (document.head || document.documentElement).appendChild(style);
}
ensureComboboxStyles();

// Global initialization function
window.initCrmSearchableSelect = function(element, options = {}) {
    if (!element) return null;
    return new CrmSearchableSelect(element, options);
};

// Global scanner that enhances all target entity selects across the CRM
window.initAllCrmSearchableSelects = function(root = document) {
    if (!root || !root.querySelectorAll) return;

    // Selector covering:
    // 1. Explicitly marked elements: [data-searchable="true"], .crm-searchable-select
    // 2. Key entity selects across modules: lead_id, product_id, installation_job_id, supplier_id, site_id, project_id, customer_id, purchase_order_id, worker_id
    const targetSelectors = [
        'select[data-searchable="true"]',
        'select.crm-searchable-select',
        'select[name="lead_id"]:not([data-searchable="false"])',
        'select[name="product_id"]:not([data-searchable="false"])',
        'select[name="installation_job_id"]:not([data-searchable="false"])',
        'select[name="supplier_id"]:not([data-searchable="false"])',
        'select[name="site_id"]:not([data-searchable="false"])',
        'select[name="project_id"]:not([data-searchable="false"])',
        'select[name="customer_id"]:not([data-searchable="false"])',
        'select[name="purchase_order_id"]:not([data-searchable="false"])',
        'select[name="service_ticket_id"]:not([data-searchable="false"])',
        'select[id="hw_scanner_lead_id"]:not([data-searchable="false"])',
        'select[id="product_id_select"]:not([data-searchable="false"])'
    ];

    const selects = root.querySelectorAll(targetSelectors.join(', '));
    selects.forEach(sel => {
        // Skip hidden, templates, or already initialized
        if (sel.dataset.crmSearchableInitialized || sel.closest('template')) return;
        new CrmSearchableSelect(sel);
    });
};

// Aliases for compatibility with earlier calls
window.initSearchableSelect = window.initCrmSearchableSelect;
window.initAllSearchableSelects = window.initAllCrmSearchableSelects;

// Auto-run on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.initAllCrmSearchableSelects();
    });
} else {
    window.initAllCrmSearchableSelects();
}

// Dynamic Observer for dynamically injected forms/modals
const domObserver = new MutationObserver((mutations) => {
    let shouldScan = false;
    for (const m of mutations) {
        if (m.addedNodes && m.addedNodes.length > 0) {
            for (const node of m.addedNodes) {
                if (node.nodeType === 1) { // Element
                    if (node.tagName === 'SELECT' || node.querySelector('select')) {
                        shouldScan = true;
                        break;
                    }
                }
            }
        }
        if (shouldScan) break;
    }
    if (shouldScan) {
        window.initAllCrmSearchableSelects();
    }
});

domObserver.observe(document.body, { childList: true, subtree: true });

export default CrmSearchableSelect;
