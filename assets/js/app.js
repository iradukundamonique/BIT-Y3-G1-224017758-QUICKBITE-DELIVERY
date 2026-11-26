(() => {
    'use strict';

    /* ---------- 1. Mobile navigation toggle ---------- */
    const toggle = document.querySelector('.nav-toggle');
    const links = document.getElementById('nav-links');
    if (toggle && links) {
        toggle.addEventListener('click', () => {
            const open = links.classList.toggle('open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    /* ---------- 2. Confirm dialogs on destructive POST forms ---------- */
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (ev) => {
            if (!window.confirm(form.dataset.confirm)) {
                ev.preventDefault();
            }
        });
    });

    /* ---------- 3. Client-side form validation (server still re-validates) ---------- */
    function clearError(field) {
        field.classList.remove('invalid');
        const next = field.nextElementSibling;
        if (next && next.classList.contains('js-error')) {
            next.remove();
        }
    }

    function showError(field, message) {
        clearError(field);
        field.classList.add('invalid');
        const span = document.createElement('span');
        span.className = 'js-error';
        span.textContent = message;
        field.insertAdjacentElement('afterend', span);
    }

    function validateField(field) {
        clearError(field);
        if (field.dataset.password !== undefined && field.value !== '') {
            if (field.value.length < 8 || !/[A-Za-z]/.test(field.value) || !/\d/.test(field.value)) {
                showError(field, 'Use at least 8 characters with a letter and a number.');
                return false;
            }
        }
        if (field.dataset.match) {
            const other = field.form.querySelector('#' + field.dataset.match);
            if (other && field.value !== other.value) {
                showError(field, 'Passwords do not match.');
                return false;
            }
        }
        if (!field.checkValidity()) {
            showError(field, field.validationMessage);
            return false;
        }
        return true;
    }

    document.querySelectorAll('form[data-validate]').forEach((form) => {
        const fields = form.querySelectorAll('input:not([type=hidden]), select, textarea');
        fields.forEach((f) => {
            f.addEventListener('blur', () => validateField(f));
            f.addEventListener('input', () => { if (f.classList.contains('invalid')) validateField(f); });
        });
        form.addEventListener('submit', (ev) => {
            let ok = true;
            let first = null;
            fields.forEach((f) => {
                if (!validateField(f)) {
                    ok = false;
                    first = first || f;
                }
            });
            // Order form: need at least one item
            if (ok && form.id === 'order-form') {
                const any = [...form.querySelectorAll('.qty-input')].some((i) => Number(i.value) > 0);
                if (!any) {
                    ok = false;
                    window.alert('Please choose at least one item.');
                }
            }
            if (!ok) {
                ev.preventDefault();
                if (first) first.focus();
            }
        });
    });

    /* ---------- Food photo upload selection feedback ---------- */
    const photoInput = document.getElementById('photo');
    const photoSelection = document.getElementById('photo-selection');
    if (photoInput && photoSelection) {
        photoInput.addEventListener('change', () => {
            const file = photoInput.files && photoInput.files[0];
            if (!file) {
                photoSelection.textContent = 'No new photo selected.';
                return;
            }
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            photoSelection.textContent = `Selected: ${file.name} (${sizeMb} MB). Press Save to upload.`;
            if (file.size > 4 * 1024 * 1024) {
                photoSelection.textContent += ' This file is larger than 4 MB.';
            }
        });
    }

    /* ---------- 4. Live order total on the menu page ---------- */
    const totalEl = document.getElementById('order-total');
    const qtyInputs = document.querySelectorAll('.qty-input');
    if (totalEl && qtyInputs.length) {
        const currency = totalEl.dataset.currency || '';
        const recalc = () => {
            let cents = 0;
            qtyInputs.forEach((i) => {
                const qty = Math.max(0, Math.min(50, parseInt(i.value, 10) || 0));
                cents += Math.round(parseFloat(i.dataset.price) * 100) * qty;
            });
            totalEl.textContent = currency + Math.round(cents / 100).toLocaleString('en-US');
        };
        qtyInputs.forEach((i) => i.addEventListener('input', recalc));
        recalc();
    }

    /* ---------- 5. Live search (Fetch + JSON API) ---------- */
    const searchInput = document.getElementById('live-search');
    const resultsBox = document.getElementById('search-results');
    if (searchInput && resultsBox) {
        const api = document.body.dataset.api;
        const showUrl = document.body.dataset.showUrl;
        let timer = null;
        let controller = null;

        const el = (tag, text, cls) => {
            const node = document.createElement(tag);
            if (text !== undefined) node.textContent = text; // textContent => no XSS
            if (cls) node.className = cls;
            return node;
        };

        const restaurantLink = (id, label) => {
            const a = el('a', label);
            a.href = showUrl + '&id=' + encodeURIComponent(id);
            return a;
        };

        function render(data) {
            resultsBox.replaceChildren();
            resultsBox.hidden = false;
            if (!data.restaurants.length && !data.items.length) {
                resultsBox.appendChild(el('p', 'No matches found.', 'muted'));
                return;
            }
            if (data.restaurants.length) {
                resultsBox.appendChild(el('h4', 'Restaurants'));
                const ul = el('ul');
                data.restaurants.forEach((r) => {
                    const li = el('li');
                    li.appendChild(restaurantLink(r.restaurant_id, r.name));
                    li.appendChild(el('span', ' — ' + r.location, 'muted'));
                    ul.appendChild(li);
                });
                resultsBox.appendChild(ul);
            }
            if (data.items.length) {
                resultsBox.appendChild(el('h4', 'Dishes'));
                const ul = el('ul');
                data.items.forEach((i) => {
                    const li = el('li');
                    li.appendChild(restaurantLink(i.restaurant_id, i.name));
                    li.appendChild(el('span', ' — ' + i.restaurant_name + ' · ' + 'RWF ' + Math.round(Number(i.price)).toLocaleString('en-US'), 'muted'));
                    ul.appendChild(li);
                });
                resultsBox.appendChild(ul);
            }
        }

        async function search(q) {
            if (controller) controller.abort();
            controller = new AbortController();
            try {
                const res = await fetch(api + '?q=' + encodeURIComponent(q), {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                render(await res.json());
            } catch (err) {
                if (err.name === 'AbortError') return;
                resultsBox.replaceChildren(el('p', 'Search is unavailable right now. Please try again.', 'muted'));
                resultsBox.hidden = false;
            }
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(timer);
            const q = searchInput.value.trim();
            if (q.length < 2) {
                if (controller) controller.abort();
                resultsBox.hidden = true;
                resultsBox.replaceChildren();
                return;
            }
            timer = setTimeout(() => search(q), 300); // debounce
        });
    }
})();
